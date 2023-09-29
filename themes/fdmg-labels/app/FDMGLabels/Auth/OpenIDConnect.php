<?php
namespace FDMGLabels\Auth;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use WP_User;

class OpenIDConnect {

	/**
	 * Defines all roles that can be granted to a user by KeyCloak
	 *
	 * @var array<string>
	 */
	public const MANAGEABLE_ROLES = array(
		Capabilities::ROLE_ACCOUNT_HOLDER,
		Capabilities::ROLE_COMPANY_LICENSED_SUBSCRIBER,
		Capabilities::ROLE_TRIAL_SUBSCRIBER,
		Capabilities::ROLE_YEARLY_SUBSCRIBER,
		// @todo Merge as 1 role, paid subscriber
	);

	public function register_hooks(): void {
		add_filter( 'openid-connect-generic-alter-user-data', array( $this, 'change_new_user_data' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'set_user_roles' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'update_user_data' ), 10, 2 );
	}

	/**
	 * Forces specific user data to be used.
	 *
	 * @param array<string,mixed> $user_data
	 * @param array<string,mixed> $openid_user_claim
	 * @return array<string,mixed>
	 */
	public function change_new_user_data( array $user_data, array $user_claim ): array {
		$user_data = array_merge( $user_data, $this->get_user_data_from_claim( $user_claim, null ) );
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
			$user_data['role'] = Capabilities::ROLE_ACCOUNT_HOLDER;
		}

		$email = $openid_user_claim['email'];
		if ( ! $user && ! email_exists( $email ) ) {
			$user_data['user_email'] = $email;
		}

		// @TODO: Replace preferred_username with FDMG User ID Field
		// If the user is new, or user_login of user_claim is different and username does not exist yet, update username of user.
		$fdmg_user_id = $openid_user_claim['preferred_username'] ?? '';
		$username     = join( '-', array( $fdmg_user_id, $email ) ); // Make username based on FDMG User ID and email
		$username     = sanitize_user( $username, true ); // Sanitize username
		$username     = substr( $username, 0, 60 ); // Make sure username does not exceed 60 characters
		if ( ! $user && ! username_exists( $username ) ) {
			$user_data['user_login'] = $username;
		}

		// If the user is new, or given_name of user_claim is different, update first name of user.
		$first_name = $openid_user_claim['given_name'] ?? '';
		if ( ! $user || $first_name !== $user->first_name ) {
			$user_data['first_name'] = $first_name;
		}

		// If the user is new, or family_name of user_claim is different, update last name of user.
		$last_name = $openid_user_claim['family_name'] ?? '';
		if ( ! $user || $last_name !== $user->last_name ) {
			$user_data['last_name'] = $last_name;
		}

		// If the user is new, or nickname of user_claim is different, update nickname of user.
		$nickname = $openid_user_claim['nickname'] ?? $email;
		if ( ! $user || $user->nickname !== $nickname ) {
			$user_data['nickname'] = $nickname;
		}

		// If the user is new, or display_name of user_claim is different, update display name of user.
		$display_name = ( $first_name && $last_name ) ? sprintf( '%s %s', $first_name, $last_name ) : $nickname;
		if ( ! $user || $user->display_name !== $display_name ) {
			$user_data['display_name'] = $display_name;
		}
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
		foreach ( $grant_roles as $role ) {
			$clarkson_user->get_user()->add_role( $role );
		}

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
		// @todo 1. Get role from user_claim
		// @todo 2. Determine WP role to grant user

		return array(
			Capabilities::ROLE_ACCOUNT_HOLDER,
		);
	}
}
