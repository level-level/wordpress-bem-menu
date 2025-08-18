<?php
namespace FDMGLabels\Auth\IdentityProviders\KeyCloak;

use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\ResponseTrait;

class KeyCloakIdentityProvider implements IdentityProviderInterface {

	use ResponseTrait;

	public function __construct( private CookieHelper $cookie_helper ) {
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

	public function handle_login_page( string $redirect_url_after_login ): never {

		// Include url parameters passed to the login page.
		$add_url_params = array_filter(
			/**
			 * Make sure that the get parameters to include only have values that can be casted to a string
			 * and are not used by the OpenID Connect plugin.
			 * @param mixed|null $param_value
			 * @param string $param_name
			 * @return bool
			 */
			$_GET, function ( $param_value, string $param_name ): bool { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				return ! in_array( $param_name, array( 'response_type', 'scope', 'client_id', 'state', 'redirect_uri' ), true )
				&& is_scalar( $param_value );
			}, ARRAY_FILTER_USE_BOTH
		);

		// Add the redirect_to parameter to the url.
		$add_url_params['redirect_to'] = rawurlencode( $redirect_url_after_login );

		// Add parameters to the OpenID Connect login url.
		$add_url_params = array_map( 'strval', $add_url_params );
		$add_url_params = array_map( 'rawurlencode', $add_url_params );
		$login_url      = add_query_arg( $add_url_params, oidcg_get_authentication_url() );

		/**
		 * Login url contains urlencoded parameters (from add_query_arg).
		 * However, the OpenID Connect plugin expects the parameters to be rawurlencoded.
		 * To make the parameters rawurlencoded, we need to replace plus (+) characters with %20.
		 * This is the main difference between rawurlencode and urlencode.
		 */
		$login_url = str_replace( '+', '%20', $login_url );

		// Redirect to the login url.
		$this->redirect_and_exit(
			$login_url,
			302,
			'FDMG Labels - Profile auth redirect to OpenID Connect',
			false
		);
	}

	public function handle_after_login_page( string $redirect_url_after_login ): never {
		// Handled by the OpenID Connect plugin.
		exit;
	}
}
