<?php

namespace FDMGLabels\Auth;

use Clarkson\WPadmin\Capabilities;
use FDMGLabels\Auth\IdentityProviders\ADvance\ADvanceIdentityProvider;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\Auth\IdentityProviders\KeyCloak\KeyCloakIdentityProvider;
use FDMGLabels\StaticPages\LoginController;
use WP_User;

class Init {

	public const META_FDMG_USER_ID         = 'fdmg_user_id';
	public const META_FDMG_ORGANISATION_ID = 'fdmg_organisation_id';
	/**
	 * Defines all roles that can be granted to a user by KeyCloak
	 *
	 * @var array<string>
	 */
	public const MANAGEABLE_ROLES = array(
		Capabilities::ROLE_FDMG_ACCOUNT_HOLDER,
		Capabilities::ROLE_FDMG_SUBSCRIBER,
	);

	/**
	 * @var IdentityProviderInterface[]
	 *
	 * This array holds the all identity providers.
	 * Should not be read directly, use $this->get_available_identity_providers() instead.
	 */
	private array $available_identity_providers = array();

	private AuthCookieHelper $auth_cookie_helper;

	public function __construct() {
		$this->auth_cookie_helper = new AuthCookieHelper();
	}

	public function register_hooks(): void {
		( new EditorCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new UserCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new SessionExpiration() )->register_hooks();
		$this->get_identity_provider()?->register_hooks();

		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_scripts' ) );
	}

	public function hide_admin_bar( bool $show ): bool {
		if ( ! $show ) {
			return $show;
		}

		if ( current_user_can( 'read' ) ) {
			return $show;
		}

		return false;
	}

	public function localize_scripts(): void {
		wp_localize_script(
			'clarkson_head',
			'auth',
			array(
				'loginUrl' => ( new LoginController() )->get_login_permalink( true ),
			)
		);
	}

	/**
	 * Returns the first available identity provider that is available.
	 */
	public function get_identity_provider(): ?IdentityProviderInterface {
		$identity_providers = $this->get_available_identity_providers();
		foreach ( $identity_providers as $identity_provider ) {
			if ( $identity_provider->is_enabled() ) {
				return $identity_provider;
			}
		}

		return null;
	}

	/**
	 * Returns an array of available identity providers in the project.
	 * @return IdentityProviderInterface[]
	 */
	private function get_available_identity_providers(): array {
		if ( ! empty( $this->available_identity_providers ) ) {
			return $this->available_identity_providers;
		}

		$this->available_identity_providers = array(
			new KeyCloakIdentityProvider( $this->auth_cookie_helper ),
			new ADvanceIdentityProvider( $this->auth_cookie_helper ),
		);
		return $this->available_identity_providers;
	}

	/**
	 * Updates a users roles from the identity provider.
	 * Only manageable roles are updated.
	 *
	 * Roles provided in $grant_roles will be added.
	 * All other roles provided that are not managable by the IDP will be deleted.
	 *
	 * @param WP_User $user
	 * @param array<value-of<self::MANAGEABLE_ROLES>> $grant_roles
	 * @return void
	 */
	public function update_user_roles_from_idp( WP_User $user, array $grant_roles ): void {
		// Grant roles that need to be granted
		foreach ( $grant_roles as $role ) {
			if ( ! in_array( $role, self::MANAGEABLE_ROLES, true ) ) {
				continue;
			}

			$user->add_role( $role );
		}

		// Remove roles the user should not have
		$user_roles = $user->roles;
		foreach ( $user_roles as $user_role ) {
			if ( ! in_array( $user_role, self::MANAGEABLE_ROLES, true ) || in_array( $user_role, $grant_roles, true ) ) {
				continue;
			}
			$user->remove_role( $user_role );
		}
	}

	public function generate_username_from_customer_id( string $prefix, string $customer_id ): string {
		$username = sprintf( '%s-%d', $prefix, $customer_id ); // Make username based on a prefixed customer ID, to prevent conflicts with WP user ids.
		$username = sanitize_user( $username, true ); // Sanitize username
		$username = substr( $username, 0, 60 ); // Make sure username does not exceed 60 characters
		return $username;
	}
}
