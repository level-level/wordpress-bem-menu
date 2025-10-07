<?php
namespace FDMGLabels\Auth\IdentityProviders\KeyCloak;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use FDMGLabels\Auth\Init as AuthInit;
use FDMGLabels\StaticPages\LoginController;
use Monolog\Registry;
use WP_Error;
use WP_User;

class OpenIDConnectHooks {

	public function register_hooks(): void {
		add_filter( 'openid-connect-generic-alter-user-data', array( $this, 'change_new_user_data' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'set_user_roles' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'update_user_data' ), 10, 2 );

		add_filter( 'wp_redirect', array( $this, 'handle_login_errors' ), 10, 2 );
		add_filter( 'openid-connect-generic-remember-me', array( $this, 'maybe_use_remember_me' ), 10, 4 );
		add_filter( 'openid-connect-generic-use-token-refresh-expiration', '__return_true' );
	}

	/**
	 * Forces specific user data to be used.
	 *
	 * @param array<string,mixed> $user_data
	 * @param array<string,mixed> $openid_user_claim
	 * @return array<string,mixed>
	 */
	public function change_new_user_data( array $user_data, array $openid_user_claim ): array {
		$user_data = array_merge( $user_data, $this->get_user_data_from_claim( $openid_user_claim, null ) );
		return $user_data;
	}

	/**
	 * Updates the user data before logging in.
	 * The OpenID Connect plugin only does this when creating the user.
	 * This function allows us to also update user data when the user already exists, so when data is changed in KeyCloak, it's also updated here.
	 *
	 * @param WP_User|false|WP_Error $user
	 * @param array<string,mixed> $openid_user_claim
	 * @return void
	 */
	public function update_user_data( $user, array $openid_user_claim ): void {
		if ( ! $user instanceof WP_User || empty( $openid_user_claim['email'] ) ) {
			return;
		}

		$user_data = $this->get_user_data_from_claim( $openid_user_claim, $user );
		if ( empty( $user_data ) ) {
			return; // Nothing needs to be updated
		}

		$user_data['ID'] = $user->ID;
		wp_update_user( $user_data );
	}

	/**
	 * Generates user data to update, from the OpenID user claim.
	 * Provide a WP_User when updating an existing user.
	 * When no WP_User is provided, role, user_email and user_login are also set.
	 *
	 * @param array<string,mixed> $openid_user_claim
	 * @param WP_User|null $user
	 * @return array<string,mixed>
	 */
	protected function get_user_data_from_claim( array $openid_user_claim, ?WP_User $user ): array {
		$user_data = array();
		if ( empty( $openid_user_claim['email'] ) ) {
			return $user_data;
		}

		if ( ! $user ) {
			/**
			 * Forces role for new user to use account holder role by default.
			 * This is needed because this class can only change roles that are added to the MANAGABLE_ROLES constant.
			 * This way the default role of a new user is inside the MANAGABLE_ROLES constant.
			 * The final role will be set when the user logs in using the openid-connect-generic-update-user-using-current-claim filter.
			 */
			$user_data['role'] = Capabilities::ROLE_FDMG_ACCOUNT_HOLDER;
		}

		$email = $openid_user_claim['email'];
		if ( ! $user && ! email_exists( $email ) ) {
			$user_data['user_email'] = $email;
		}

		// If the user is new, or user_login of user_claim is different and username does not exist yet, update username of user.
		$fdmg_user_id = (int) ( $openid_user_claim['customerNumber'] ?? 0 );
		$username     = ( new AuthInit() )->generate_username_from_customer_id( 'customer', (string) $fdmg_user_id ); // 'customer' as a prefix for backward compatibility
		if ( ! $user && ! username_exists( $username ) ) {
			$user_data['user_login'] = $username;
		}

		// If the user is new, or given_name of user_claim is different, update first name of user.
		$first_name = $openid_user_claim['firstName'] ?? '';
		if ( ! $user || $first_name !== $user->first_name ) {
			$user_data['first_name'] = $first_name;
		}

		// If the user is new, or family_name of user_claim is different, update last name of user.
		$last_name = $openid_user_claim['lastName'] ?? '';
		if ( ! $user || $last_name !== $user->last_name ) {
			$user_data['last_name'] = $last_name;
		}

		// If the user is new, or determined display_name from user_claim is different, update display name of user.
		$display_name = $openid_user_claim['fullName'] ?? '';
		$display_name = $display_name ?: ( ( $first_name && $last_name ) ? sprintf( '%s %s', $first_name, $last_name ) : '' );
		$display_name = $display_name ?: $email;
		if ( ! $user || $user->display_name !== $display_name ) {
			$user_data['display_name'] = $display_name;
		}

		// If the user is new, or determined nickname from user_claim is different, update display name of user.
		$nickname = $display_name;
		if ( ! $user || $user->nickname !== $nickname ) {
			$user_data['nickname'] = $nickname;
		}

		// Update FDMG User ID and Organisation ID
		$user_data['meta_input'] = array(
			AuthInit::META_FDMG_USER_ID         => $fdmg_user_id,
			AuthInit::META_FDMG_ORGANISATION_ID => (int) ( $openid_user_claim['organizationId'] ?? 0 ),
		);
		return $user_data;
	}

	/**
	 * Set a users roles based on the OpenID user claim data.
	 * First adds the roles that should be granted.
	 * Then removes all roles that are in the MANAGABLE_ROLES constant and not in the roles that should be granted.
	 *
	 * @param WP_User|false|WP_Error $user
	 * @param array<string,mixed> $openid_user_claim
	 * @return void
	 */
	public function set_user_roles( $user, array $openid_user_claim ): void {
		if ( ! $user instanceof WP_User ) {
			return;
		}

		$clarkson_user = Objects::get_instance()->get_user( $user );
		$grant_roles   = $this->determine_roles_to_grant_from_claim( $openid_user_claim );
		( new AuthInit() )->update_user_roles_from_idp( $clarkson_user->get_user(), $grant_roles );
	}

	/**
	 * Determines the roles that should be granted to a user based on the OpenID user claim data.
	 * Should always at least return one role.
	 *
	 * @param array<string,mixed> $openid_user_claim
	 * @return non-empty-array<value-of<AuthInit::MANAGEABLE_ROLES>>
	 */
	protected function determine_roles_to_grant_from_claim( array $openid_user_claim ): array {
		$fdmg_roles = $openid_user_claim['roles'] ?? array();
		if ( in_array( 'VIEWER', $fdmg_roles, true ) ) {
			return array( Capabilities::ROLE_FDMG_SUBSCRIBER );
		}

		return array( Capabilities::ROLE_FDMG_ACCOUNT_HOLDER );
	}

	/**
	 * Parses the access token and returns the data inside it.
	 *
	 * @param string $access_token
	 * @return array<string,mixed>
	 */
	protected function parse_access_token( string $access_token ): array {
		$access_token_parts = explode( '.', $access_token );
		if ( count( $access_token_parts ) < 2 ) {
			return array();
		}

		$extracted_access_token_data = json_decode(
			base64_decode( // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
				str_replace( // Because token is encoded in base64 URL (and not just base64).
					array( '-', '_' ),
					array( '+', '/' ),
					$access_token_parts[1]
				)
			),
			true
		);
		if ( ! is_array( $extracted_access_token_data ) ) {
			return array();
		}
		return $extracted_access_token_data;
	}

	/**
	 * Redirect to login URL on error.
	 *
	 * @param string $location
	 * @param int $status
	 * @return string
	 */
	public function handle_login_errors( string $location, int $status ): string {
		// Determine if using OpenID connect
		$is_openid_connect_request = filter_input( INPUT_GET, 'action' ) === 'openid-connect-authorize';
		if ( ! $is_openid_connect_request ) {
			return $location;
		}

		// If not redirecting to wp login url, do nothing
		if ( ! str_starts_with( $location, wp_login_url() ) || $status !== 302 ) {
			return $location;
		}

		// If the redirect contains no login error, do nothing
		$query = wp_parse_args( (string) wp_parse_url( $location, PHP_URL_QUERY ) );
		if ( ! isset( $query['login-error'] ) ) {
			return $location;
		}

		// Get errors from the url
		$error_code    = $query['login-error'];
		$error_message = $query['message'] ?? '';

		// Log the error
		Registry::wordpress()->warning(
			'Error on OpenID Connect login attempt',
			array(
				'code'    => $error_code,
				'message' => $error_message,
			)
		);

		// Redirect to the error page with the error code and message
		$redirect_url = ( new LoginController() )->get_login_error_permalink(
			$error_code,
			$error_message,
		);
		return $redirect_url;
	}

	/**
	 * Determines if the "remember me" functionality of OpenID Connect should be used based on the ID token claim.
	 * This will allow the token refresh expiration to be used as the expiration of the WordPress cookie.
	 *
	 * It checks if rememberMe is defined in the ID token claim.
	 *
	 * @param bool $use_remember_me
	 * @param WP_User $user
	 * @param array<string,mixed> $token_response
	 * @param array<string,mixed> $id_token_claim
	 * @return bool
	 */
	public function maybe_use_remember_me( bool $use_remember_me, WP_User $user, array $token_response, array $id_token_claim ): bool {
		if ( ! array_key_exists( 'rememberMe', $id_token_claim ) ) {
			return $use_remember_me;
		}

		return (bool) $id_token_claim['rememberMe'];
	}
}
