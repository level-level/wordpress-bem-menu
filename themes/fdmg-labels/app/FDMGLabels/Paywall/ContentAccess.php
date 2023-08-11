<?php
namespace FDMGLabels\Paywall;

use Clarkson_Core\WordPress_Object\user;
use WP_Post;
use WP_User;
use Clarkson\WPadmin\Capabilities;

class ContentAccess {

	/**
	 * @var array<Capabilities::ROLE_*> Roles that can bypass the paywall.
	 */
	public const ALLOWED_ROLES = array();

	public function register_hooks(): void {
		add_filter( 'll_paywall_user_can_access_restricted_post', array( $this, 'check_user_can_access' ), 10, 4 );
	}

	/**
	 * Users should only be able to access the post if they have the right role.
	 *
	 * @param bool $user_has_access
	 * @param WP_Post $post Post object.
	 * @param WP_User|null $user User object.
	 * @param array<string,mixed> $additional_data Additional data that can be used to check if a user has permission.
	 * @return bool
	 */
	public function check_user_can_access( bool $user_has_access, WP_Post $post, ?WP_User $user, array $additional_data ): bool {
		if ( ! $user instanceof WP_User ) {
			return false;
		}

		$user = new user( $user );

		// If the user can edit posts, allow
		if ( $user->get_user()->has_cap( 'edit_posts' ) ) {
			return true;
		}

		// If user has one of the allowed roles, allow
		$user_roles = $user->get_roles();
		foreach ( self::ALLOWED_ROLES as $allowed_role ) {
			if ( in_array( $allowed_role, $user_roles, true ) ) {
				return true;
			}
		}
		return false;
	}

}
