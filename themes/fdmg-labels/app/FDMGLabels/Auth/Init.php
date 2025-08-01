<?php

namespace FDMGLabels\Auth;

use FDMGLabels\Auth\IdentityProviders\ADvance\ADvanceIdentityProvider;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\Auth\IdentityProviders\KeyCloak\KeyCloakIdentityProvider;
use FDMGLabels\StaticPages\LoginController;

class Init {

	public const META_FDMG_USER_ID         = 'fdmg_user_id';
	public const META_FDMG_ORGANISATION_ID = 'fdmg_organisation_id';

	/**
	 * @var IdentityProviderInterface[]
	 *
	 * This array holds the available identity providers.
	 * Should not be read directly, use get_available_identity_providers() instead.
	 */
	private array $available_identity_providers = array();

	public function __construct(
		private AuthCookieHelper $auth_cookie_helper,
	) {}

	public function register_hooks(): void {
		( new EditorCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new UserCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new SessionExpiration() )->register_hooks();

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
			if ( $identity_provider->is_available() ) {
				return $identity_provider;
			}
		}

		return null;
	}

	/**
	 * Returns an array of available identity providers.
	 *
	 * @return IdentityProviderInterface[]
	 */
	private function get_available_identity_providers(): array {
		if ( ! empty( $this->available_identity_providers ) ) {
			return $this->available_identity_providers;
		}

		$this->available_identity_providers = array(
			new KeyCloakIdentityProvider( $this->auth_cookie_helper ),
			new ADvanceIdentityProvider(),
		);
		return $this->available_identity_providers;
	}
}
