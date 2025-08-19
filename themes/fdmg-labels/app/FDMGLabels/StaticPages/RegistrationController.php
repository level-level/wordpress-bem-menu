<?php

namespace FDMGLabels\StaticPages;

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
			case self::PAGE_ERROR:
				$this->set_page_title( __( 'Registration Error', 'fdmg-labels' ) );
				$this->handle_registration_error();
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

		// Redirect to the login url.
		$this->redirect_and_exit( home_url(), 302, 'FDMG Labels - Profile auth not handled by identity provider', false );
	}

	protected function handle_registration_error(): void {
		// Logic for handling registration errors goes here
	}
}
