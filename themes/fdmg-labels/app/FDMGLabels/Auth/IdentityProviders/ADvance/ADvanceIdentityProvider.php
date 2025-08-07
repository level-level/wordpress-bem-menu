<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	public function get_id(): string {
		return 'advance';
	}

	public function register_hooks(): void {}

	public function is_enabled(): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_ENABLED ) );
	}

	public function handle_login_page( string $redirect_url_after_login ): void {

		$handle_login_url = home_url();
		$handle_login_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_url_after_login ), $handle_login_url );

		// Redirect to the login url.
		wp_redirect( $handle_login_url, 302, 'FDMG Labels - Profile auth redirect to ADvance' ); // phpcs:ignore
		wp_die();
	}
}
