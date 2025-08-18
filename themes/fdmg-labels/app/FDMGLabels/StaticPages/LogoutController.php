<?php
namespace FDMGLabels\StaticPages;

class LogoutController extends AbstractMainController {

	public const PAGE_LOGOUT = '';

	public function get_slug(): string {
		return 'logout';
	}

	/**
	 * Route to the correct handle function based on the query var.
	 */
	public function handle_request( string $query_var ): void {
		switch ( $query_var ) {
			case self::PAGE_LOGOUT:
				$this->set_page_title( __( 'Logout', 'fdmg-labels' ) );
				$this->handle_logout_link();
				break; // @phpstan-ignore deadCode.unreachable
		}
	}

	/**
	 * Redirect to the WordPress logout page.
	 */
	protected function handle_logout_link(): never {
		// Disable caching
		nocache_headers();

		// Determine redirect url.
		$redirect_to = home_url();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// Redirect to the WP logout url. Your current login status does not matter.
		$url = wp_specialchars_decode( wp_logout_url( $redirect_to ) );
		$this->redirect_and_exit( $url, 302, 'FDMG Labels - Profile logout' );
	}
}
