<?php

namespace FDMGLabels\API;

use Clarkson\Utilities\IPAddress;
use FDMGLabels\Auth\IPAddressIDPRedirect;
use FDMGLabels\StaticPages\LoginController;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class LoginRedirectController extends WP_REST_Controller {

	/** @var string */
	private $version;

	public function __construct() {
		$this->version   = 'v1';
		$this->namespace = 'fdmg-labels';
		$this->rest_base = 'login-redirect';
		$this->schema    = array();
	}

	public function register_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Registers the routes.
	 */
	public function register_routes(): void {
		register_rest_route(
			$this->namespace . '/' . $this->version,
			'/' . $this->rest_base . '/ip-to-idp',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'redirect_ip_to_idp' ),
					'permission_callback' => '__return_true',
					'args'                => $this->get_collection_params(),
				),
				'schema' => array( $this, 'get_public_item_schema' ),
			)
		);
	}

	/**
	 * Return an url to redirect the user to the IDP if needed.
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 *
	 * @return WP_Error|WP_REST_Response Response object on success, or WP_Error object on failure.
	 */
	public function redirect_ip_to_idp( $request ) {
		// If user is already logged in, no need to redirect
		if ( is_user_logged_in() ) {
			return $this->get_redirect_response( null );
		}

		// Get and validate the visitor IP address
		$ip_util            = new IPAddress();
		$visitor_ip_address = $ip_util->get_visitor_ip_address();
		if ( ! $visitor_ip_address || ! $ip_util->validate_ip_address( $visitor_ip_address ) ) {
			return $this->get_redirect_response( null );
		}

		// If no IDP alias was found, no need to redirect
		$idp_alias = ( new IPAddressIDPRedirect() )->ip_address_to_idp_alias( $visitor_ip_address );
		if ( $idp_alias === null ) {
			return $this->get_redirect_response( null );
		}

		// Build the redirect URL
		$redirect_to_after_login = $request->get_param( 'redirect_to' );
		$redirect_url            = ( new LoginController() )->get_login_permalink( $redirect_to_after_login );
		$redirect_url            = add_query_arg( 'kc_idp_hint', rawurlencode( $idp_alias ), $redirect_url );

		// Return the response
		return $this->get_redirect_response(
			$redirect_url
		);
	}

	protected function get_redirect_response( ?string $redirect_url ): WP_REST_Response|WP_Error {
		$response = rest_ensure_response(
			array(
				'redirect_url' => $redirect_url,
			)
		);
		return $response;
	}
}
