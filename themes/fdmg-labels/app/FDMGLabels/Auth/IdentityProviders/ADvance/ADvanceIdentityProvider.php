<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\StaticPages\LoginController;
use FDMGLabels\StaticPages\ResponseTrait;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	use ResponseTrait;

	public function get_id(): string {
		return 'advance';
	}

	public function register_hooks(): void {}

	public function is_enabled(): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_ENABLED ) );
	}

	public function handle_login_page( string $redirect_url_after_login ): void {
		// Determine our handle login url, including the url to redirect to after the login
		$handle_login_url = home_url() . '/login/' . LoginController::PAGE_AFTER_LOGIN;

		// Pass handle url to ADvance login page to get redirectred back to our platform and handle the login
		$idp_login_url = get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ) );
		$idp_login_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_url_after_login ), $idp_login_url );
		$idp_login_url = add_query_arg( 'returl', rawurlencode( $handle_login_url ), $idp_login_url );

		// Redirect to the login url.
		$this->redirect_and_exit(
			$idp_login_url,
			302,
			'FDMG Labels - Profile auth redirect to ADvance',
			false
		);
	}
}
