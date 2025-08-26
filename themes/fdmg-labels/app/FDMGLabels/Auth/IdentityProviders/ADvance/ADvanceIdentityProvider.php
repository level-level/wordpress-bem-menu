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
use FDMGLabels\StaticPages\RegistrationController;
use FDMGLabels\StaticPages\ResponseTrait;
use Monolog\Registry;
use Ramsey\Uuid\Uuid;
use WP_Error;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	public const META_ADVANCE_USER_ID = 'advance_user_id';
	public const META_USER_TOKEN      = 'advance_user_token';

	use ResponseTrait;

	public function __construct( private CookieHelper $cookie_helper ) {
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
		$this->auto_login( $redirect_url_after_login );
	}

	public function handle_registration_page( string $redirect_url_after_registration ): never {
		// Set concurrency cookie
		if ( ! $this->cookie_helper->get_cookie( 'ADConCtrlCookie' ) ) {
			$this->cookie_helper->set_cookie( 'ADConCtrlCookie', Uuid::uuid7()->toString(), time() + YEAR_IN_SECONDS, true );
		}

		// Determine our handle login url, including the url to redirect to after the login
		$registration_controller = new RegistrationController();
		$handle_register_url     = $registration_controller->get_permalink( RegistrationController::PAGE_AFTER_REGISTER );

		// Pass handle url to ADvance login page to get redirectred back to our platform and handle the login
		$idp_base_url  = get_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ) );
		$idp_login_url = sprintf( '%s/register', untrailingslashit( $idp_base_url ) );
		$idp_login_url = add_query_arg( 'returl', rawurlencode( $handle_register_url ), $idp_login_url );

		// Redirect to the login url.
		$this->redirect_and_exit(
			$idp_login_url,
			302,
			'FDMG Labels - Profile auth redirect to ADvance',
			false
		);
	}

	public function handle_after_registration_page( string $redirect_url_after_registration ): never {
		$this->auto_login( $redirect_url_after_registration );
	}

	/**
	 * Run autologin on ADvance API. This is run both on login and on register because we want to immediately log someone in.
	 */
	public function auto_login( string $redirect_url_after_login ): never {
		$login_controller = new LoginController();
		// Validate frontend auth cookie is set
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

		// Validate concurrency cookie is set
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

		// Handle error response code
		$status_code = $response->getStatusCode();
		if ( $status_code !== 200 ) {
			Registry::wordpress()->warning(
				'ADvance login API returned unexpected status code',
				array(
					'status_code' => $status_code,
					'response'    => (string) $response->getBody(),
				)
			);

			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					sprintf( 'API response returned unexpected status code %s', $status_code )
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		// Parse response to JSON
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

		if ( $body['status']['code'] === 'ERROR' ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					$body['message']
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}
		// Handle success response, but still an error occurred in processing
		if ( $body['response']['status']['code'] === 'ERROR' ) {
			Registry::wordpress()->warning(
				'ADvance login API returned error response',
				array(
					'response' => (string) $response->getBody(),
				)
			);

			$messages = array_map(
				function ( array $message ): string {
					return sprintf( '%s: %s', $message['type'] ?? 'unknown', $message['msg'] ?? 'unknown' );
				},
				$body['response']['status']['messages'] ?? array()
			);
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					implode( ', ', $messages )
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		// Handle unactivated users
		if ( ! $body['response']['data']['LoginDetails']['Activated'] ) {
			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'activation_error',
					'User account is not activated.'
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		// Create or update WP user
		try {
			$user_id = $this->create_or_update_user( $body['response'] );
		} catch ( Exception $e ) {
			Registry::wordpress()->warning(
				'ADvance login API user creation/update failed',
				array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				)
			);

			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'user_creation_error',
					$e->getMessage()
				),
				302, 'FDMG Labels - Profile auth user creation error',
			);
		}

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
		$auth_init     = new AuthInit();
		$login_details = $response['data']['LoginDetails'];

		$wp_user_data = array(
			'user_email'   => $login_details['Username'],
			'display_name' => $login_details['FirstName'] . ' ' . $login_details['Surname'],
			'first_name'   => $login_details['FirstName'],
			'last_name'    => $login_details['Surname'],
			'meta_input'   => array(
				self::META_ADVANCE_USER_ID => $login_details['PartyID'],
				self::META_USER_TOKEN      => $login_details['UserToken'],
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
			$wp_user_data['user_login'] = $auth_init->generate_username_from_customer_id( 'advance-customer', $login_details['PartyID'] );
			$wp_user_data['role']       = Capabilities::ROLE_FDMG_ACCOUNT_HOLDER;
			$user_id                    = wp_insert_user( $wp_user_data );
			if ( $user_id instanceof WP_Error ) {
				throw new Exception( 'Failed to create user: ' . $user_id->get_error_message() );
			}
		}

		$clarkson_user = user::get( $user_id );
		if ( ! $clarkson_user ) {
			throw new Exception( 'Created user cannot be found: ' . $user_id );
		}

		$grant_roles = array( Capabilities::ROLE_FDMG_ACCOUNT_HOLDER ); // Always account holder, no subscriptions
		$auth_init->update_user_roles_from_idp( $clarkson_user->get_user(), $grant_roles );
		return $user_id;
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
