<?php
namespace FDMGLabels\Auth\IdentityProviders\KeyCloak;

use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\CookieHelper;

class KeyCloakIdentityProvider implements IdentityProviderInterface {

	private CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function get_id(): string {
		return 'keycloak';
	}

	public function register_hooks(): void {
		( new OpenIDConnectHooks( $this->cookie_helper ) )->register_hooks();
	}

	public function is_enabled(): bool {
		return function_exists( 'oidcg_get_authentication_url' );
	}
}
