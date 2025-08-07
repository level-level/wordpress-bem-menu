<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use Clarkson\Utilities\IPAddress;
use Exception;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\LoginAPI;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\LoginController;
use FDMGLabels\StaticPages\ResponseTrait;
use Ramsey\Uuid\Uuid;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	use ResponseTrait;

	private CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function get_id(): string {
		return 'advance';
	}

	public function register_hooks(): void {}

	public function is_enabled(): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_ENABLED ) );
	}

	public function handle_login_page( string $redirect_url_after_login ): void {
		// Set concurrency cookie
		$this->cookie_helper->set_cookie( 'ADConCtrlCookie', Uuid::uuid7()->toString(), time() + YEAR_IN_SECONDS, true );

		// Determine our handle login url, including the url to redirect to after the login
		$login_controller = new LoginController();
		$handle_login_url = $login_controller->get_permalink( LoginController::PAGE_AFTER_LOGIN );

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

	public function handle_after_login_page( string $redirect_url_after_login ): void {
		$login_controller = new LoginController();
		$frontend_auth_cookie = $this->cookie_helper->get_cookie( 'ADFEAuthCookie' );
		if (! is_string( $frontend_auth_cookie ) || empty( $frontend_auth_cookie ) ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'no_frontend_cookie',
					'No frontend cookie.'
				),
				302, 'FDMG Labels - Profile auth no frontend cookie',
			);
		}

		$concurrency_cookie = $this->cookie_helper->get_cookie( 'ADConCtrlCookie' );
		if ( ! is_string( $concurrency_cookie ) || empty( $concurrency_cookie ) ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'no_concurrency_cookie',
					'No concurrency cookie.'
				),
				302, 'FDMG Labels - Profile auth no concurrency cookie',
			);
		}
;
		$client = ( new ClientFactory() )->get_client();
		$api = new LoginAPI( $client );
		try {
			$response = $api->auto_login(
				$frontend_auth_cookie,
				$concurrency_cookie,
				( new IPAddress() )->get_visitor_ip_address()
			);
		} catch (Exception $e) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					$e->getMessage()
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}
	}
}
