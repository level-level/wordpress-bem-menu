<?php
namespace FDMGLabels\StaticPages;

class LoginController extends AbstractMainController {

	public const PAGE_LOGIN = '';
	public function get_slug(): string {
		return 'login';
	}

	/**
	 * Route to the correct handle function based on the query var.
	 */
	public function handle_request( string $query_var ): void {
		switch ( $query_var ) {
			case self::PAGE_LOGIN:
				$this->set_page_title( __( 'Login', 'fdmg-labels' ) );
				$this->handle_login_link();
				break;
		}
	}

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
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

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
		$this->redirect_and_exit( oidcg_get_authentication_url(), 302, 'FDMG Labels - Profile auth redirect to OpenID Connect' );
	}
}
