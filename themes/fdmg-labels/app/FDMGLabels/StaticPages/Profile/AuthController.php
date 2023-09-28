<?php
namespace FDMGLabels\StaticPages\Profile;

use FDMGLabels\StaticPages\BaseChildStaticPageController;

class AuthController extends BaseChildStaticPageController {

	public const SUB_PAGE_LOGIN       = 'login';
	public const SUB_PAGE_LOGOUT       = 'logout';

	/**
	 * Catch all requests for the magic login and run the corresponding actions.
	 */
	public function handle_request( string $query_var ): void {
		switch ( $query_var ) {
			case self::SUB_PAGE_LOGIN:
				$this->set_page_title( __( 'Login', 'fdmg-labels' ) );
				$this->handle_login_link();
				break;
			case self::SUB_PAGE_LOGOUT:
				$this->set_page_title( __( 'Logout', 'fdmg-labels' ) );
				$this->handle_logout_link();
				break;
		}
	}

	/**
	 * Redirect user to the keyclock login page.
	 */
	protected function handle_login_link(): void {
		// Disable caching
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Determine redirect url.
		$redirect_to = home_url();
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

		// Redirect to the login url.
		$url = oidcg_get_authentication_url();
		$this->redirect_and_exit( oidcg_get_authentication_url(), 302, 'FDMG Labels - Profile auth redirect to OpenID Connect' );
	}

	/**
	 * Redirect to the WordPress logout page.
	 */
	protected function handle_logout_link(): void {
		// Disable caching
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Determine redirect url.
		$redirect_to = home_url();
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		}

		// If already logged out, directly go to the redirect url.
		if ( ! is_user_logged_in() ) {
			$this->redirect_and_exit( $redirect_to, 302, 'FDMG Labels - Profile auth already logged out' );
		}

		// Redirect to the WP logout url.
		$url = wp_specialchars_decode( wp_logout_url( $redirect_to ) );
		$this->redirect_and_exit( $url, 302, 'FDMG Labels - Profile logout' );
	}
}
