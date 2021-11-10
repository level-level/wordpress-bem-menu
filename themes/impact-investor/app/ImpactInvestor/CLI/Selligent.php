<?php

namespace ImpactInvestor\CLI;

use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\post;
use ImpactInvestor\Connectors\Selligent\Client;

class Selligent {
	/**
	 * Shows information about the current status of the API.
	 */
	public function status( array $args, array $args_assoc ): void {
		\WP_CLI::line( 'User: ' . constant( 'II_SELLIGENT_USER' ) );
		\WP_CLI::line( 'Pass: ' . ( defined( 'II_SELLIGENT_USER' ) ? 'Password is set' : 'Password is not set' ) );
		\WP_CLI::line( 'Type: ' . Client::get_type() );
	}

	/**
	 * Registers a post to Selligent
	 *
	 * ## Options
	 *
	 * <id>...
	 * : One or more IDs of posts to send
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand post-article
	 */
	public function post_article( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		$client   = new Client();
		$post_ids = $this->filter_post_ids( $args );

		/** @var Clarkson_Object[] */
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
			\WP_CLI::line( 'Sending post with ID ' . $post->get_id() );
			$result = $client->register_post( $post );
			$this->echo_response( $result );
			\WP_CLI::line( '' );
		}
	}

	/**
	 * Combines posts into a newsletter that is registered to Selligent.
	 *
	 * ## Options
	 *
	 * <id>...
	 * : One or more IDs of posts to send
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand register-newsletter
	 */
	public function register_newsletter( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		$client   = new Client();
		$post_ids = $this->filter_post_ids( $args );

		if ( empty( $post_ids ) ) {
			\WP_CLI::error( 'No valid posts were given' );
			exit;
		}

		$this->echo_response( $client->register_newsletter( $post_ids ) );
	}

	/**
	 * Sends the last registered newsletter to all recipients.
	 *
	 * ## Options
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand send-newsletter
	 */
	public function send_newsletter( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );
		$client = new Client();

		if ( ! \WP_CLI\Utils\get_flag_value( $args_assoc, 'preview', false ) ) {
			\WP_CLI::warning( 'This action triggers sending the newsletter to all recipients.' );
			\WP_CLI::line( 'Current newsletter type is: ' . Client::get_type() );
			\WP_CLI::confirm( 'Are you sure?' );
		}

		$this->echo_response( $client->send_newsletter() );
	}

	/**
	 * Handles flags that are valid for all commands.
	 */
	protected function handle_default_flags( array $args_assoc ): void {
		if ( \WP_CLI\Utils\get_flag_value( $args_assoc, 'preview', false ) ) {
			add_filter(
				'pre_http_request',
				/**
				 * @param mixed $false Can be anything, if this value is not
				 * false, it will prevent the request from running.
				 */
				function( $false, array $args, string $url ) {
					return $this->preview_request( $false, $args, $url );
				}, 10, 3
			);
		}
	}

	/**
	 * Filters into the pre_http_request action to prevent the request from
	 * actually being sent, but allows us to preview the values.
	 *
	 * @param mixed $pre Originally a false value, but can become anything.
	 * If a non-false value is returned, the request will not be made.
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
		$json = wp_json_encode( $args, JSON_PRETTY_PRINT );
		\WP_CLI::line( $json ?: 'Error encoding JSON string' );
		\WP_CLI::line( '' );

		$pre = array(
			'response' => array(
				'code'    => 0,
				'message' => 'Not sent',
			),
			'body'     => 'The data was not sent to Selligent because the --preview flag was set.',
		);

		return $pre;
	}

	protected function echo_response( array $data ): void {
		\WP_CLI::line( sprintf( 'Response: %d %s', $data['response']['code'], $data['response']['message'] ) );
		\WP_CLI::line( $data['body'] );
	}

	/**
	 * Filters out al non-integers and 0 values.
	 *
	 * @return int[]
	 */
	protected function filter_post_ids( array $args ): array {
		$post_ids = array();

		foreach ( $args as $arg ) {
			if ( (int) $arg !== 0 ) {
				$post_ids[] = (int) $arg;
			}
		}

		return $post_ids;
	}
}
