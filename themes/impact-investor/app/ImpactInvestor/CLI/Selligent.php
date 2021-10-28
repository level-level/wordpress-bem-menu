<?php

namespace ImpactInvestor\CLI;

use Clarkson_Core\WordPress_Object\post;
use ImpactInvestor\Connectors\Selligent\Client;

class Selligent {
	/**
	 * <id>...
	 * : One or more IDs of posts to send
	 *
	 * @subcommand post-article
	 */
	public function post_article( array $args, array $args_assoc ): void {
		$client   = new Client();
		$post_ids = array_filter( $args, 'is_int' );

		$this->handle_default_flags( $args_assoc );

		$posts = array();
		foreach ( $post_ids as $post_id ) {
			$post = post::get( $post_id );

			if ( ! is_null( $post ) ) {
				$posts[] = $post;
			}
		}

		if ( empty( $posts ) ) {
			\WP_CLI::error( 'No valid posts were given' );
			exit;
		}

		foreach ( $posts as $post ) {
			$result = $client->register_post( $post );
			$this->echo_response( $result );
		}
	}

	/**
	 * <id>...
	 * : One or more IDs of posts to send
	 *
	 * @subcommand register-newsletter
	 */
	public function register_newsletter( array $args, array $args_assoc ): void {
		$client   = new Client();
		$post_ids = array_filter( $args, 'is_numeric' );

		$this->handle_default_flags( $args_assoc );

		if ( empty( $post_ids ) ) {
			\WP_CLI::error( 'No valid posts were given' );
			exit;
		}

		$this->echo_response( $client->register_newsletter( $post_ids ) );
	}

	/**
	 * Handles flags that are valid for all commands.
	 */
	protected function handle_default_flags( array $args_assoc ): void {
		if ( \WP_CLI\Utils\get_flag_value( $args_assoc, 'preview', false ) ) {
			add_filter( 'pre_http_request', function( $false, $args, $url ) {
				$this->preview_request( $false, $args, $url );
			}, 10, 3 );
		}
	}

	/**
	 * Filters into the pre_http_request action to prevent the request from
	 * actually being sent, but allows us to preview the values.
	 * 
	 * @param mixed $pre Originally a false value, but can become anything.
	 * If a non-false value is returned, the request willnot be made.
	 *
	 * @param array $args  Complete list of values that will be sent in the
	 * request.
	 *
	 * @param string $url  The url the request will be sent to.
	 */
	protected function preview_request( $pre, array $args, string $url ): array {
		\WP_CLI::line( 'Request URL:' );
		\WP_CLI::line( $url );
		\WP_CLI::line( '' );
		\WP_CLI::line( 'Request:' );
		\WP_CLI::line( wp_json_encode( $args, JSON_PRETTY_PRINT ) );
		\WP_CLI::line( '' );
		
		$pre = array(
			'response' => array(
				'code'    => 0,
				'message' => 'Not sent'
			),
			'body' => 'The data was not sent to Selligent because the --preview flag was set.'
		);

		return $pre;
	}

	protected function echo_response( array $data ): void {
		\WP_CLI::line( sprintf( 'Response: %d %s', $data['response']['code'], $data['response']['message'] ) );
		\WP_CLI::line( $data['body'] );
	}
}
