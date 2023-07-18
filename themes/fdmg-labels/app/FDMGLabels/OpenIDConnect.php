<?php
namespace FDMGLabels;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use WP_User;

class OpenIDConnect {
	/**
	 * Defines all roles that can be granted to a user
	 *
	 * @var array<string>
	 */
	public const MANAGEABLE_ROLES = array(
		Capabilities::ROLE_ACCOUNT_HOLDER,
		Capabilities::ROLE_COMPANY_LICENSED_SUBSCRIBER, //!
		Capabilities::ROLE_TRIAL_SUBSCRIBER, //! 
		Capabilities::ROLE_YEARLY_SUBSCRIBER, //!
		// @todo Merge as 1 role, paid subscriber
	);

	public function register_hooks(): void {
		// https://github.com/oidc-wp/openid-connect-generic/blob/dev/HOWTO.md#openid-connect-generic-user-create
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'set_user_roles' ), 10, 2 );
		add_action( 'openid-connect-generic-update-user-using-current-claim', array( $this, 'update_user_data' ), 10, 2 );
		add_filter( 'openid-connect-generic-alter-user-data', array( $this, 'change_new_user_data' ), 10, 2 );
	}

	/**
	 * Undocumented function
	 *
	 * @param WP_User|false|WP_Error $user
	 */
	public function set_user_roles( $user, array $user_claim ): void {
		if ( ! $user instanceof WP_User ) {
			return;
		}
		$clarkson_user = Objects::get_instance()->get_user( $user );
		// Get roles from user_claim and add them as WP roles
		$grant_roles   = $this->determine_roles_to_grant_from_claim( $user_claim );
		foreach ( $grant_roles as $role ) {
			$clarkson_user->get_user()->add_role( $role );
		}
		// Remove old roles that are no longer in user_claim
		$user_roles = $clarkson_user->get_roles();
		foreach ( $user_roles as $user_role ) {
			if ( in_array( $user_role, self::MANAGEABLE_ROLES, true ) && ! in_array( $user_role, $grant_roles, true ) ) {
				$clarkson_user->get_user()->remove_role( $user_role );
			}
		}
	}

	/**
	 * Undocumented function
	 *
	 * @param array $user_claim
	 * @return array
	 */
	protected function determine_roles_to_grant_from_claim( array $user_claim ): array {
		$test = $user_claim;
		// @todo 1. Get role from user_claim
		// @todo 2. Determine WP role to grant user
		return array();
	}

	/**
	 * Undocumented function
	 *
	 * @param WP_User|false|WP_Error $user
	 */
	public function update_user_data( $user, array $user_claim ): void {
		if ( ! $user instanceof WP_User || empty( $openid_user_claim['email'] ) ) {
			return;
		}
		$user_data = $this->get_user_data_from_claim( $user_claim, $user );
		if ( empty( $user_data ) ) {
			return; // Nothing needs to be updated
		}
		$user_data['ID'] = $user->ID;
		wp_update_user( $user_data );
	}

	/**
	 * Undocumented function
	 *
	 * @param array $user_claim
	 * @param WP_User|null $user
	 * @return array
	 */
	protected function get_user_data_from_claim( array $user_claim, ?WP_User $user): array {
		$user_data = array();
		if ( empty( $openid_user_claim['email'] ) ) {
			return $user_data;
		}

		if ( ! $user ) {
			/**
			 * Forces role for new user to use account holder role by default.
			 * This is needed because this class can only change roles that are added to the MANAGEABLE_ROLES constant.
			 * This way the default role of a new user is inside the MANAGEABLE_ROLES constant.
			 * The final role will be set when the user logs in using the openid-connect-generic-update-user-using-current-claim filter.
			 */
			$user_data['role'] = Capabilities::ROLE_ACCOUNT_HOLDER;
		}

		$email = $user_claim['email'];
		if ( ! $user && ! email_exists( $email ) ) {
			$user_data['user_email'] = $email;
		}

		$username = sanitize_user( $email, false );
		if ( ! $user && ! username_exists( $username ) ) {
			$user_data['user_login'] = $username;
		}

		$first_name = $user_claim['given_name'] ?? '';
		if ( ! $user || $first_name !== $user->first_name ) {
			$user_data['first_name'] = $first_name;
		}

		$last_name = $user_claim['family_name'] ?? '';
		if ( ! $user || $last_name !== $user->last_name ) {
			$user_data['last_name'] = $last_name;
		}

		$nickname = $user_claim['nickname'] ?? $email;
		if ( ! $user || $user->nickname !== $nickname ) {
			$user_data['nickname'] = $nickname;
		}

		$display_name = ( $first_name && $last_name ) ? sprintf( '%s %s', $first_name, $last_name ) : $nickname;
		if ( ! $user || $user->display_name !== $display_name ) {
			$user_data['display_name'] = $display_name;
		}
		return $user_data;
	}
	
	/**
	 * Forces specific user data to be used.
	 *
	 * @param array $user_data
	 * @param array $openid_user_claim
	 * @return array
	 */
	public function change_new_user_data( array $user_data, array $user_claim ): array {
		$user_data = array_merge( $user_data, $this->get_user_data_from_claim( $user_claim, null ) );
		return $user_data;
	}
}
