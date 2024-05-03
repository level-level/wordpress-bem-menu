<?php

namespace FDMGLabels\API;

use Clarkson\WPadmin\Capabilities;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\base_object;
use FDMGLabels\Comments\Comments;
use WP_Error;
use WP_Post;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

class PageFragmentsController extends WP_REST_Controller {

	public function __construct() {
		$this->namespace = 'fdmg-labels/v1';
		$this->rest_base = 'page-fragments';
		$this->schema    = $this->get_item_schema();
	}

	public function register_hooks(): void {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
		add_filter( 'rest_authentication_errors', array( $this, 'remove_nonce_check' ), 1 );
		add_filter( 'rest_send_nocache_headers', array( $this, 'send_nocache_headers' ) );
	}

	public function register_routes(): void {
		register_rest_route(
			$this->namespace,
			$this->rest_base,
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);

		register_rest_route(
			$this->namespace,
			$this->rest_base . '/(?P<post_id>[\d]+)',
			array(
				array(
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_item' ),
					'permission_callback' => '__return_true',
				),
			)
		);
	}

	/**
	 * Remove nonce check for this endpoint, as there is a low risk of CSRF attacks.
	 * CSRF attacks have the goal to modify data on the server.
	 * But because this is a read-only endpoint, there is no risk of that.
	 *
	 * @param mixed $result
	 * @return mixed
	 */
	public function remove_nonce_check( $result ) {
		if ( strstr( $_SERVER['REQUEST_URI'], $this->namespace . '/' . $this->rest_base ) ) {
			remove_filter( 'rest_authentication_errors', 'rest_cookie_check_errors', 100 );
		}
		return $result;
	}

	/**
	 * Send nocache headers for this endpoint.
	 * The content can be unique for every visitor.
	 */
	public function send_nocache_headers( bool $send ): bool {
		if ( strstr( $_SERVER['REQUEST_URI'], $this->namespace . '/' . $this->rest_base ) ) {
			return true;
		}
		return $send;
	}

	/**
	 * Get the content of the post that was initially blocked by the access wall.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_item( $request ) {
		$post = null;
		if ( ! empty( $request['post_id'] ) ) {
			$post = get_post( (int) $request['post_id'] );
			if ( ! $post instanceof WP_Post ) {
				return new WP_Error(
					'rest_invalid_post_id',
					__( 'Invalid Post ID.', 'll-paywall' ),
					array( 'status' => 404 )
				);
			}
		}

		/** @var base_object|null */
		$object    = $post ? Objects::get_instance()->get_object( $post ) : null;
		$fragments = array();
		$fragments = $this->maybe_add_comment_form( $fragments, $object );

		return rest_ensure_response(
			$fragments
		);
	}

	/**
	 * Add comment form to fragments.
	 *
	 * @param array<string,string> $fragments
	 * @param base_object|null $clarkson_object
	 * @return array<string,string>
	 */
	protected function maybe_add_comment_form( array $fragments, ?base_object $clarkson_object ): array {
		// Only include comment form for posts
		if ( ! $clarkson_object ) {
			return $fragments;
		}

		// Only include comment form if comments are enabled for post
		if ( $clarkson_object->get_object()->comment_status !== 'open' ) {
			return $fragments;
		}

		// Include subscription required html if user has no permission to write comments
		if ( ! current_user_can( Capabilities::CAP_WRITE_COMMENTS ) ) {
			$fragments['comment_form'] = ( new Comments() )->get_subscription_required_html( $clarkson_object );
			return $fragments;
		}

		// Include comment form
		$fragments['comment_form'] = $clarkson_object->get_comment_form();
		return $fragments;
	}
}
