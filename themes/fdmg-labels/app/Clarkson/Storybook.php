<?php

namespace Clarkson;

use Clarkson\Theme\SVGIcons;
use Clarkson\WPadmin\Tools\Styleguide;
use Clarkson_Core\Templates;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class Storybook extends WP_REST_Controller {

	public function __construct() {
		$this->schema    = $this->get_item_schema();
		$this->rest_base = 'storybook';
		$this->namespace = 'wp/v2';
	}

	public function register_hooks(): void {
		if ( Styleguide::is_enabled() ) {
			add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		}
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace, '/' . $this->rest_base . '/stories', array(
				array(
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'stories' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * @return WP_REST_Response|WP_Error|int
	 */
	public function stories( WP_REST_Request $request ) {
		$path    = $request->get_param( 'path' ) ?: '';
		$objects = $request->get_param( 'params' ) ?: array();

		$objects['svg_icons'] = SVGIcons::get_instance();

		$template = Templates::get_instance()->render_twig( $path, $objects, true );

		$response = new WP_REST_Response();
		$response->set_data( $template );

		return $response;
	}
}
