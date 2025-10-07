<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use Clarkson\Utilities\IPAddress;
use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\WordPress_Object\user;
use DateTimeImmutable;
use Exception;
use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;
use FDMGLabels\Auth\Init as AuthInit;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\LoginAPI;
use FDMGLabels\Connectors\ADvance\UserAPI;
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
			$this->cookie_helper->set_cookie( 'ADConCtrlCookie', Uuid::uuid7()->toString(), time() + YEAR_IN_SECONDS, true, $this->get_cookie_domain() );
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
			$this->cookie_helper->set_cookie( 'ADConCtrlCookie', Uuid::uuid7()->toString(), time() + YEAR_IN_SECONDS, true, $this->get_cookie_domain() );
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

		// Login user in ADvance
		$client = ( new ClientFactory() )->get_client();
		$api    = new LoginAPI( $client );
		try {
			$login_response = $api->auto_login(
				$frontend_auth_cookie,
				$concurrency_cookie,
				( new IPAddress() )->get_visitor_ip_address()
			);
		} catch ( Exception $e ) {
			Registry::wordpress()->warning(
				'Error on Advance login attempt',
				array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
				)
			);

			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					$e->getMessage()
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		// Handle error response code
		$status_code = $login_response->getStatusCode();
		if ( $status_code !== 200 ) {
			Registry::wordpress()->warning(
				'ADvance login API returned unexpected status code',
				array(
					'status_code' => $status_code,
					'response'    => (string) $login_response->getBody(),
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
			$body = $api->parse_response_to_json( $login_response );
		} catch ( Exception $e ) {
			Registry::wordpress()->warning(
				'ADvance login API response could not be parsed',
				array(
					'code'     => $e->getCode(),
					'message'  => $e->getMessage(),
					'response' => (string) $login_response->getBody(),
				)
			);

			$this->redirect_and_exit(
				$login_controller->get_login_error_permalink(
					'api_error',
					'Failed to parse API response: ' . $e->getMessage()
				),
				302, 'FDMG Labels - Profile auth API error',
			);
		}

		// Handle success response, but still an error occurred in processing
		if ( $body['response']['status']['code'] === 'ERROR' ) {
			Registry::wordpress()->warning(
				'ADvance login API returned error response',
				array(
					'response' => (string) $login_response->getBody(),
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
				'ADvance user creation/update failed',
				array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
					'trace'   => $e->getTraceAsString(),
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

		// Login user in WordPress
		wp_clear_auth_cookie();
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, false, '' );

		// Redirect back to where user came from
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
	 * @throws Exception
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

		$grant_roles = $this->get_roles_to_grant_to_user( $login_details['UserToken'] );
		$auth_init->update_user_roles_from_idp( $clarkson_user->get_user(), $grant_roles );
		return $user_id;
	}

	/**
	 * Redirect to the WordPress logout page.
	 */
	public function handle_logout_page( string $redirect_url_after_logout ): never {
		// Delete the Frontend Auth Cookie
		$this->cookie_helper->clear_cookie( 'ADFEAuthCookie', true, $this->get_cookie_domain() );

		// Redirect to the WP logout url. Your current login status does not matter.
		$url = wp_specialchars_decode( wp_logout_url( $redirect_url_after_logout ) );
		$this->redirect_and_exit( $url, 302, 'FDMG Labels - Profile logout' );
	}

	/**
	 * Determines the roles a user should be granted based on the subscriptions returned from the api.
	 *
	 * @param string $user_token
	 * @throws Exception
	 * @return array<value-of<AuthInit::MANAGEABLE_ROLES>>
	*/
	private function get_roles_to_grant_to_user( string $user_token ): array {
		$client = ( new ClientFactory() )->get_client();
		$api    = new UserAPI( $client );

		try {
			$user_subscriptions_response = $api->get_user_subscriptions(
				$user_token,
				null,
				( new IPAddress() )->get_visitor_ip_address()
			);
		} catch ( Exception $e ) {
			Registry::wordpress()->warning(
				'Error on Advance get user subscriptions attempt',
				array(
					'code'    => $e->getCode(),
					'message' => $e->getMessage(),
					'trace'   => $e->getTraceAsString(),
				)
			);
			throw new Exception(
				'Failed to get user subscriptions',
				$e->getCode(),
				$e
			);
		}

		// Handle error response code
		$status_code = $user_subscriptions_response->getStatusCode();
		if ( $status_code !== 200 ) {
			Registry::wordpress()->warning(
				'ADvance user API returned unexpected status code',
				array(
					'status_code' => $status_code,
					'response'    => (string) $user_subscriptions_response->getBody(),
				)
			);

			throw new Exception(
				sprintf( 'API response returned unexpected status code: %s', $status_code ),
				$status_code
			);
		}

		// Parse response to JSON
		try {
			$body = $api->parse_response_to_json( $user_subscriptions_response );
		} catch ( Exception $e ) {
			Registry::wordpress()->warning(
				'ADvance user API response could not be parsed',
				array(
					'code'     => $e->getCode(),
					'message'  => $e->getMessage(),
					'response' => (string) $user_subscriptions_response->getBody(),
				)
			);

			throw new Exception(
				'Failed to parse API response: ' . $e->getMessage(),
				$e->getCode(),
				$e
			);
		}

		// Handle success response, but still an error occurred in processing
		if ( $body['response']['status']['code'] === 'ERROR' ) {
			Registry::wordpress()->warning(
				'ADvance user API returned error response',
				array(
					'code'     => $body['response']['status']['code'],
					'messages' => $body['response']['status']['messages'],
					'response' => (string) $user_subscriptions_response->getBody(),
				)
			);

			$messages = array_map(
				function ( array $message ): string {
					return sprintf( '%s: %s', $message['type'] ?? 'unknown', $message['msg'] ?? 'unknown' );
				},
				$body['response']['status']['messages'] ?? array()
			);
			throw new Exception(
				'API returned error response: ' . implode( ', ', $messages )
			);
		}

		// Loop subscriptions and if a subscription is valid, return subscriber role
		$configuration                    = get_field( ADvance::OPTION_GROUP, 'option' ) ?: array();
		$subscription_product_rows        = $configuration[ ADvance::OPTION_SUBSCRIPTION_PRODUCTS ] ?? array();
		$allowed_subscription_product_ids = array_map(
			function ( array $product ): string {
				return (string) ( $product[ ADvance::OPTION_SUBSCRIPTION_PRODUCT_ID ] ?? '' );
			},
			$subscription_product_rows
		);
		foreach ( $body['response']['data']['list'] as $subscription ) {
			if ( ! in_array( $subscription['ProductID'], $allowed_subscription_product_ids, true ) ) {
				continue;
			}

			if ( $subscription['SubscriptionStatus'] !== 'Live' ) {
				continue;
			}

			$now                     = new DateTimeImmutable( 'now', wp_timezone() );
			$subscription_start_date = new DateTimeImmutable( $subscription['StartDateTime'] );
			$subscription_end_date   = new DateTimeImmutable( $subscription['GraceEndDateTime'] ?: $subscription['EndDateTime'] );
			if ( $subscription_start_date > $now || $subscription_end_date < $now ) {
				continue;
			}

			return array( Capabilities::ROLE_FDMG_SUBSCRIBER );
		}

		// No valid subscriptions found, return account holder role
		return array( Capabilities::ROLE_FDMG_ACCOUNT_HOLDER );
	}

	/**
	 * Get ADvance cookie domain.
	 * The ADvance cookie domain is the root domain of the WordPress domain,
	 * without any subdomain prefix.
	 * It is then prefixed with a dot, to also support older browsers.
	 *
	 * @see https://www.php.net/manual/en/function.setcookie.php
	 *
	 * @return string
	 */
	private function get_cookie_domain(): string {
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : null;
		if ( ! $cookie_domain ) {
			$cookie_domain = wp_parse_url( home_url(), PHP_URL_HOST );
		}
		if ( ! $cookie_domain ) {
			return '';
		}

		// Get domain parts
		$domain_parts = explode( '.', $cookie_domain );
		if ( count( $domain_parts ) < 2 ) {
			return $cookie_domain;
		}

		// Determine the root domain and tld.
		$tld                     = array_pop( $domain_parts );
		$root_domain_without_tld = array_pop( $domain_parts );
		$check_double_tld        = sprintf( '%s.%s', $root_domain_without_tld, $tld );
		$double_tlds             = array( 'co.uk', 'org.uk', 'gov.uk', 'ac.uk', 'net.uk' );
		if ( count( $domain_parts ) && in_array( $check_double_tld, $double_tlds, true ) ) {
			$tld                     = $check_double_tld;
			$root_domain_without_tld = array_pop( $domain_parts );
		}

		// Return the cookie domain.
		return sprintf( '.%s.%s', $root_domain_without_tld, $tld );
	}
}
