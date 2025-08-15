<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\ResponseTrait;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	use ResponseTrait;

	public function __construct( private CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function get_id(): string {
		return 'advance';
	}

	public function register_hooks(): void {}

	public function is_enabled(): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_ENABLED ) );
	}

	public function handle_login_page( string $redirect_url_after_login ): never {

		$handle_login_url = home_url();
		$handle_login_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_url_after_login ), $handle_login_url );

		// Redirect to the login url.
		$this->redirect_and_exit(
			$handle_login_url,
			302,
			'FDMG Labels - Profile auth redirect to ADvance',
			false
		);
	}

	/**
	 * Handles the after login page. And logs the user in if the credentials are correct from the api.
	 *
	 * @param string $redirect_url_after_login
	 * @return never
	 */
	public function handle_after_login_page( string $redirect_url_after_login ): never {
		// Custom handling should be added here
		exit;
	}

	/**
	 * Redirect to the WordPress logout page.
	 */
	public function handle_logout_page( string $redirect_url_after_logout ): never {
		// Delete the Frontend Auth Cookie
		$this->cookie_helper->clear_cookie( 'ADFEAuthCookie' );

		// Redirect to the WP logout url. Your current login status does not matter.
		$url = wp_specialchars_decode( wp_logout_url( $redirect_url_after_logout ) );
		$this->redirect_and_exit( $url, 302, 'FDMG Labels - Profile logout' );
	}
}
