<?php

namespace FDMGLabels\StaticPages\IDPKeycloak;

use FDMGLabels\StaticPages\AbstractMainController;

class IDPKeycloak extends AbstractMainController {
	/**
	 * Redirect user to the keyclock login page.
	 */
	protected function handle_login_link(): void {
		// Disable caching
		nocache_headers();

		// Determine redirect url.
		$redirect_to = home_url();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		}

		// If already logged in, directly go to the redirect url.
		if ( is_user_logged_in() ) {
			$this->redirect_and_exit( $redirect_to, 302, 'FDMG Labels - Profile auth already logged in' );
			return;
		}

		// If authentication is not available, redirect to the home url.
		if ( ! function_exists( 'oidcg_get_authentication_url' ) ) {
			$this->redirect_and_exit( home_url(), 302, 'FDMG Labels - Profile auth OpenID Connect not enabled' );
		}

		$add_url_params = array_filter(
			/**
			 * Make sure that the get parameters to include only have values that can be casted to a string
			 * and are not used by the OpenID Connect plugin.
			 * @param mixed|null $param_value
			 * @param string $param_name
			 * @return bool
			 */
			$_GET, function ( $param_value, string $param_name ): bool {
				return ! in_array( $param_name, array( 'response_type', 'scope', 'client_id', 'state', 'redirect_uri' ), true )
				&& is_scalar( $param_value );
			}, ARRAY_FILTER_USE_BOTH
		);
		$add_url_params = array_map( 'strval', $add_url_params );
		$add_url_params = array_map( 'rawurlencode', $add_url_params );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Get the login url.
		$login_url = add_query_arg( $add_url_params, oidcg_get_authentication_url() );

		/**
		 * Login url contains urlencoded parameters (from add_query_arg).
		 * However, the OpenID Connect plugin expects the parameters to be rawurlencoded.
		 * To make the parameters rawurlencoded, we need to replace plus (+) characters with %20.
		 * This is the main difference between rawurlencode and urlencode.
		 */
		$login_url = str_replace( '+', '%20', $login_url );

		// Redirect to the login url.
		$this->redirect_and_exit( $login_url, 302, 'FDMG Labels - Profile auth redirect to OpenID Connect', false );
	}

	public function get_slug(): string {
		return '';
	}

	public function handle_request(string $query_var): void {

	}
}
