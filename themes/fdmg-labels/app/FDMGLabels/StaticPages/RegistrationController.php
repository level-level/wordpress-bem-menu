<?php

namespace FDMGLabels\StaticPages;

use Clarkson\Theme\Navigation\Navigation;
use FDMGLabels\Auth\Init;
use FDMGLabels\StaticPages\AbstractMainController;

class RegistrationController extends AbstractMainController {

	public const PAGE_REGISTER       = '';
	public const PAGE_AFTER_REGISTER = 'after-register';
	public const PAGE_ERROR          = 'registration-error';

	public function get_slug(): string {
		return 'register';
	}

	/**
	 * Route to the correct handle function based on the query var.
	 */
	public function handle_request( string $query_var ): void {
		switch ( $query_var ) {
			case self::PAGE_REGISTER:
				$this->set_page_title( __( 'Register', 'fdmg-labels' ) );
				$this->handle_registration_link();
				break;
			case self::PAGE_AFTER_REGISTER:
				$this->set_page_title( __( 'Registration Complete', 'fdmg-labels' ) );
				$this->handle_after_registration();
				break;
		}
	}

	public function get_login_error_permalink( string $code, string $message ): string {
		$permalink = $this->get_permalink( self::PAGE_ERROR );
		$permalink = add_query_arg(
			array(
				'code'    => rawurlencode( $code ),
				'message' => rawurlencode( $message ),
			), $permalink
		);
		return $permalink;
	}

	protected function handle_registration_link(): void {
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
		}

		$identity_provider = ( new Init() )->get_identity_provider();
		if ( $identity_provider ) {
			$identity_provider->handle_registration_page( $redirect_to );
		}

		// Redirect to the home URL if no IDP handles the request
		$this->redirect_and_exit( home_url(), 302, 'FDMG Labels - Profile auth not handled by identity provider', false );
	}

	protected function handle_after_registration(): void {
		// Determine redirect url.
		$redirect_to = home_url();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		}

		// If already logged in, directly go to the redirect url.
		if ( is_user_logged_in() ) {
			$this->redirect_and_exit( $redirect_to, 302, 'FDMG Labels - Profile auth already logged in' );
		}

		$identity_provider = ( new Init() )->get_identity_provider();
		if ( $identity_provider ) {
			$identity_provider->handle_after_registration_page( $redirect_to );
		}

		// Redirect to the home URL if no IDP handles the request
		$this->redirect_and_exit( home_url(), 302, 'FDMG Labels - Profile auth not handled by identity provider', false );
	}

	/**
	 * Get the permalink of the registration page.
	 * If chosen, it will redirect to the current url after the user has registered.
	 *
	 * @param bool|string $redirect_to_current_url False to not redirect. True to redirect to the current url. String to redirect to a specific url.
	 * @return string
	 */
	public function get_registration_permalink( $redirect_to_current_url ): string {
		$permalink = $this->get_permalink( self::PAGE_REGISTER );
		if ( $redirect_to_current_url === false ) {
			return $permalink;
		}

		if ( $redirect_to_current_url === true ) {
			$https       = is_ssl() ? 'https' : 'http';
			$host        = $_SERVER['HTTP_HOST'] ?? '';
			$request_uri = $_SERVER['REQUEST_URI'] ?? '';
			$redirect_to = $https . '://' . $host . $request_uri;
		} else {
			$redirect_to = $redirect_to_current_url;
		}

		$permalink = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $permalink );
		return $permalink;
	}

	/**
	 * Show the user a registration error page containing the provided error details.
	 */
	protected function handle_registration_error(): never {
		// Disable caching
		nocache_headers();

		$code             = filter_input( INPUT_GET, 'code' ) ?: __( 'Unknown error code', 'fdmg-labels' );
		$message          = filter_input( INPUT_GET, 'message' ) ?: __( 'Unknown error message', 'fdmg-labels' );
		$registration_url = $this->get_registration_permalink( false );
		$home_url         = home_url();

		$this->echo_twig_and_exit(
			'static-pages/registration-error.twig', array(
				'navigation'       => new Navigation(),
				'error_code'       => $code,
				'error_message'    => $message,
				'registration_url' => $registration_url,
				'home_url'         => $home_url,
			)
		);
	}
}
