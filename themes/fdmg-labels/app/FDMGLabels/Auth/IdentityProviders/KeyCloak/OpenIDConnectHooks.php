<?php
namespace FDMGLabels\Auth\IdentityProviders\KeyCloak;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use FDMGLabels\Auth\Init as AuthInit;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\LoginController;
use Monolog\Registry;
use WP_Error;
use WP_User;

class OpenIDConnectHooks {

	protected const REMEMBER_ME_COOKIE_NAME = 'fdmg_idp_remember_me';

	protected CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	/**
	 * Defines all roles that can be granted to a user by KeyCloak
	 *
	 * @var array<string>
	 */
	public const MANAGEABLE_ROLES = array(
		Capabilities::ROLE_FDMG_ACCOUNT_HOLDER,
		Capabilities::ROLE_FDMG_SUBSCRIBER,
	);

	public function register_hooks(): void {
		add_filter( 'openid-connect-generic-alter-user-data', array( $this, 'change_new_user_data' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'set_user_roles' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'update_user_data' ), 10, 2 );

		add_action( 'openid-connect-generic-user-logged-in', array( $this, 'create_rememberme_cookie_after_login' ) );
		add_action( 'wp_logout', array( $this, 'remove_rememberme_cookie_after_logout' ) );

		add_filter( 'wp_redirect', array( $this, 'handle_login_errors' ), 10, 2 );
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
		$username     = sprintf( 'customer-%d', $fdmg_user_id ); // Make username based on a prefixed FDMG customer ID, to prevent conflicts with WP user ids.
		$username     = sanitize_user( $username, true ); // Sanitize username
		$username     = substr( $username, 0, 60 ); // Make sure username does not exceed 60 characters
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

		// Grant roles that need to be granted
		foreach ( $grant_roles as $role ) {
			$clarkson_user->get_user()->add_role( $role );
		}

		// Remove roles the user should not have
		$user_roles = $clarkson_user->get_roles();
		foreach ( $user_roles as $user_role ) {
			if ( in_array( $user_role, self::MANAGEABLE_ROLES, true ) && ! in_array( $user_role, $grant_roles, true ) ) {
				$clarkson_user->get_user()->remove_role( $user_role );
			}
		}
	}

	/**
	 * Determines the roles that should be granted to a user based on the OpenID user claim data.
	 * Should always at least return one role.
	 *
	 * @param array<string,mixed> $openid_user_claim
	 * @return non-empty-array<string>
	 */
	protected function determine_roles_to_grant_from_claim( array $openid_user_claim ): array {
		$fdmg_roles = $openid_user_claim['roles'] ?? array();
		if ( in_array( 'VIEWER', $fdmg_roles, true ) ) {
			return array( Capabilities::ROLE_FDMG_SUBSCRIBER );
		}

		return array( Capabilities::ROLE_FDMG_ACCOUNT_HOLDER );
	}

	/**
	 * After a user is successfully logged in using openid connect, set a cookie
	 * on the user indicating that the user should be remembered. This is
	 * then used in JavaScript to redirect the user to the login page when the
	 * user is not logged in and the cookie is present.
	 */
	public function create_rememberme_cookie_after_login( WP_User $user ): void {
		$token_response = get_user_meta( $user->ID, 'openid-connect-generic-last-token-response', true );
		if ( ! is_array( $token_response ) ) {
			return;
		}

		$access_token      = $token_response['access_token'] ?? '';
		$access_token_data = $this->parse_access_token( $access_token );

		$remember_me   = (bool) ( $access_token_data['rememberMe'] ?? false );
		$seconds_valid = (int) ( $token_response['refresh_expires_in'] ?? 0 );
		if ( ! $remember_me ) {
			return;
		}

		$this->cookie_helper->set_cookie(
			self::REMEMBER_ME_COOKIE_NAME,
			'1',
			time() + $seconds_valid,
			false,
		);
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
	 * When a user logs out by it's own choice, remove the remember me cookie.
	 */
	public function remove_rememberme_cookie_after_logout(): void {
		$this->cookie_helper->clear_cookie( self::REMEMBER_ME_COOKIE_NAME, false );
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
}
