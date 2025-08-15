<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use Clarkson\Utilities\IPAddress;
use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\WordPress_Object\user;
use Exception;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\Auth\Init as AuthInit;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\LoginAPI;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\LoginController;
use FDMGLabels\StaticPages\ResponseTrait;
use Ramsey\Uuid\Uuid;
use WP_Error;

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

	public function handle_login_page( string $redirect_url_after_login ): never {
		// Set concurrency cookie
		if ( ! $this->cookie_helper->get_cookie( 'ADConCtrlCookie' ) ) {
			$this->cookie_helper->set_cookie( 'ADConCtrlCookie', Uuid::uuid7()->toString(), time() + YEAR_IN_SECONDS, true );
		}

		// Determine our handle login url, including the url to redirect to after the login
		$login_controller = new LoginController();
		$handle_login_url = $login_controller->get_permalink( LoginController::PAGE_AFTER_LOGIN );
		$handle_login_url = add_query_arg( 'redirect_to', rawurlencode( $redirect_url_after_login ), $handle_login_url );

		// Pass handle url to ADvance login page to get redirectred back to our platform and handle the login
		$idp_base_url  = get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ) );
		$idp_login_url = sprintf( '%s/login', untrailingslashit( $idp_base_url ) );
		$idp_login_url = add_query_arg( 'returl', rawurlencode( $handle_login_url ), $idp_login_url );

		// Redirect to the login url.
		$this->redirect_and_exit(
			$idp_login_url,
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
		$login_controller     = new LoginController();
		$frontend_auth_cookie = $this->cookie_helper->get_cookie( 'ADFEAuthCookie' ) ?: '';
		if ( ! is_string( $frontend_auth_cookie ) || empty( $frontend_auth_cookie ) ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'no_frontend_cookie',
					'No frontend cookie.'
				),
				302, 'FDMG Labels - Profile auth no frontend cookie',
			);
		}

		$concurrency_cookie = $this->cookie_helper->get_cookie( 'ADConCtrlCookie' ) ?: '';
		if ( ! is_string( $concurrency_cookie ) || empty( $concurrency_cookie ) ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'no_concurrency_cookie',
					'No concurrency cookie.'
				),
				302, 'FDMG Labels - Profile auth no concurrency cookie',
			);
		}

		$client = ( new ClientFactory() )->get_client();
		$api    = new LoginAPI( $client );
		try {
			$response = $api->auto_login(
				$frontend_auth_cookie,
				$concurrency_cookie,
				( new IPAddress() )->get_visitor_ip_address()
			);
		} catch ( Exception $e ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					$e->getMessage()
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		try {
			$body = $api->parse_response_to_json( $response );
		} catch ( Exception $e ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					'Failed to parse API response: ' . $e->getMessage()
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		if ( $body['status']['code'] === 'ERROR' || $response->getStatusCode() !== 200 ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					$body['message']
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		$user_id = $this->create_or_update_user( $body['response'] );
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false, '' );
		$this->redirect_and_exit(
			$redirect_url_after_login,
			302,
			'FDMG Labels - Profile auth successful',
		);
	}

	/**
	 * Creates or updates a user from the api.
	 *
	 * @param array<string,mixed> $response
	 * @return int
	*/
	public function create_or_update_user( array $response ): int {
		$login_details = $response['data']['LoginDetails'];

		$wp_user_data = array(
			'user_email'   => $login_details['Username'],
			'user_login'   => $login_details['Username'],
			'display_name' => $login_details['FirstName'] . ' ' . $login_details['Surname'],
			'first_name'   => $login_details['FirstName'],
			'last_name'    => $login_details['Surname'],
			'meta_input'   => array(
				'party_id' => $login_details['PartyID'],
			),
		);

		$user = get_user_by( 'email', $login_details['Username'] );
		if ( $user ) {
			$wp_user_data['ID'] = $user->ID;
			$user_id            = wp_update_user( $wp_user_data );
			if ( $user_id instanceof WP_Error ) {
				throw new Exception( 'Failed to update user: ' . $user_id->get_error_message() );
			}
		} else {
			$wp_user_data['role'] = Capabilities::ROLE_FDMG_ACCOUNT_HOLDER;
			$user_id              = wp_insert_user( $wp_user_data );
			if ( $user_id instanceof WP_Error ) {
				throw new Exception( 'Failed to create user: ' . $user_id->get_error_message() );
			}
		}
		$grant_roles   = array( Capabilities::ROLE_FDMG_ACCOUNT_HOLDER );
		$clarkson_user = user::get( $user_id );

		if ( ! $clarkson_user ) {
			return 0;
		}

		// Grant roles that need to be granted
		foreach ( $grant_roles as $role ) {
			$clarkson_user->get_user()->add_role( $role );
		}

		// Remove roles the user should not have
		$user_roles = $clarkson_user->get_roles();
		foreach ( $user_roles as $user_role ) {
			if ( in_array( $user_role, AuthInit::MANAGEABLE_ROLES, true ) && ! in_array( $user_role, $grant_roles, true ) ) {
				$clarkson_user->get_user()->remove_role( $user_role );
			}
		}

		return $user_id;
	}
}
