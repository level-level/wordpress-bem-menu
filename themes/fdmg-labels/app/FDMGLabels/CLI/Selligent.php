<?php

namespace FDMGLabels\CLI;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use FDMGLabels\Connectors\Selligent\Client;
use FDMGLabels\Connectors\Selligent\Settings\NewsletterPublicationSettings;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use WP_CLI;
use WP_Error;

use function WP_CLI\Utils\get_flag_value;

class Selligent {
	/**
	 * Shows information about the current status of the API.
	 */
	public function status( array $args, array $args_assoc ): void {
		$settings = Settings::get_instance();
		WP_CLI::line( 'Base url: ' . $settings->get_base_url() );
		WP_CLI::line( 'User: ' . $settings->get_username() );
		WP_CLI::line( 'Password: ' . ( ! empty( $settings->get_password() ) ? 'Yes' : 'No' ) );
		WP_CLI::line( 'Newsletter list ID: ' . $settings->get_list_ids()->get_newsletter() );
		WP_CLI::line( 'Article list ID: ' . $settings->get_list_ids()->get_article() );

		$types = array_map(
			function( $type ) {
				return $type->get_type();
			}, $settings->get_newsletter()->get_types()
		);
		WP_CLI::line( 'Types: ' . implode( ',', $types ) );

		// Get publication names as string
		$publications = array_map(
			function( NewsletterPublicationSettings $publication ) {
				return $publication->get_name();
			}, $settings->get_newsletter()->get_publications()
		);
		WP_CLI::line( 'Publications: ' . implode( ',', $publications ) );
	}

	/**
	 * Registers a post to Selligent
	 *
	 * ## Options
	 *
	 * <post_ids>...
	 * : One or more IDs of posts to send, separated by a comma.
	 *
	 * <newsletter_id>...
	 * : Newsletter ID
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand register-article
	 */
	public function register_article( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		$client   = new Client();
		$post_ids = $this->filter_post_ids( $args[0] ?? '' );

		/** @var base_object[] */
		$posts = array();
		foreach ( $post_ids as $post_id ) {
			$post = Clarkson_Object::get( $post_id );

			if ( $post instanceof base_object ) {
				$posts[] = $post;
			}
		}

		if ( empty( $posts ) ) {
			WP_CLI::error( 'No valid posts were given' );
			exit;
		}

		$newsletter_id = (int) ( $args[1] ?? 0 );
		$newsletter    = ii_newsletter::get( $newsletter_id );
		if ( ! $newsletter instanceof ii_newsletter ) {
			WP_CLI::error( 'No valid newsletter ID was given' );
			exit;
		}

		foreach ( $posts as $post ) {
			WP_CLI::line( 'Sending post with ID ' . $post->get_id() );
			$result = $client->register_post( $post, $newsletter );
			$this->echo_response( $result );
			WP_CLI::line( '' );
		}
	}

	/**
	 * Combines posts into a newsletter that is registered to Selligent.
	 *
	 * ## Options
	 *
	 * <id>
	 * : Newsletter ID to send
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * [--test-mode]
	 * : Sends the newsletter as test
	 * ---
	 * default: 1
	 * options:
	 *   - 0
	 *   - 1
	 *   - 2
	 * ---
	 *
	 * @subcommand register-newsletter
	 */
	public function register_newsletter( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		/**
		 * @var Newsletter::META_SEND_TEST_OPTION_*
		 */
		$test_mode = (string) get_flag_value( $args_assoc, 'test-mode', Newsletter::META_SEND_TEST_OPTION_PREVIEW );

		$newsletter_id = (int) ( $args[0] ?? 0 );
		$newsletter    = ii_newsletter::get( $newsletter_id );
		if ( ! $newsletter instanceof ii_newsletter ) {
			WP_CLI::error( 'No valid newsletter ID was given' );
			exit;
		}

		$client = new Client();
		$this->echo_response( $client->register_newsletter( $newsletter, $test_mode ) );
	}

	/**
	 * Sends the last registered newsletter to all recipients.
	 *
	 * ## Options
	 *
	 * <id>
	 * : Newsletter ID to send
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand send-newsletter
	 */
	public function send_newsletter( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		$newsletter_id = (int) ( $args[0] ?? 0 );
		$newsletter    = ii_newsletter::get( $newsletter_id );
		if ( ! $newsletter instanceof ii_newsletter ) {
			WP_CLI::error( 'No valid newsletter ID was given' );
			exit;
		}

		$client = new Client();

		if ( ! get_flag_value( $args_assoc, 'preview', false ) ) {
			WP_CLI::warning( 'This action triggers sending the newsletter to all recipients.' );
			WP_CLI::line( 'Current newsletter publication is: ' . $newsletter->get_publication() );
			WP_CLI::line( 'Current newsletter type is: ' . $newsletter->get_type() );
			WP_CLI::confirm( 'Are you sure?' );
		}

		$this->echo_response( $client->send_newsletter( $newsletter ) );
	}

	/**
	 * Subscribes the provided e-mail address to the newsletter.
	 *
	 * ## Options
	 *
	 * <email>
	 * : E-mail address to subscribe
	 *
	 * [--preview]
	 * : Preview the body data without sending the request.
	 *
	 * @subcommand optin-email
	 */
	public function optin_email( array $args, array $args_assoc ): void {
		$this->handle_default_flags( $args_assoc );

		if ( ! filter_var( $args[0], FILTER_VALIDATE_EMAIL ) ) {
			WP_CLI::error( 'No valid e-mail address was given' );
			exit;
		}

		$client = new Client();
		$this->echo_response( $client->optin_email( $args[0] ) );
	}

	/**
	 * Handles flags that are valid for all commands.
	 */
	protected function handle_default_flags( array $args_assoc ): void {
		if ( get_flag_value( $args_assoc, 'preview', false ) ) {
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
		WP_CLI::line( 'Request URL:' );
		WP_CLI::line( $url );
		WP_CLI::line( '' );
		WP_CLI::line( 'Request:' );
		$json = wp_json_encode( $args, JSON_PRETTY_PRINT );
		WP_CLI::line( $json ?: 'Error encoding JSON string' );
		WP_CLI::line( '' );

		$pre = array(
			'response' => array(
				'code'    => 0,
				'message' => 'Not sent',
			),
			'body'     => 'The data was not sent to Selligent because the --preview flag was set.',
		);

		return $pre;
	}

	/**
	 * Echo the response
	 *
	 * @param array|WP_Error $data
	 * @return void
	 */
	protected function echo_response( $data ): void {
		if ( $data instanceof WP_Error ) {
			WP_CLI::line( sprintf( 'Error: %s %s', (string) $data->get_error_code(), $data->get_error_message() ) );
			WP_CLI::line( wp_json_encode( $data->get_all_error_data(), JSON_PRETTY_PRINT ) ?: '' );
		} else {
			WP_CLI::line( sprintf( 'Response: %d %s', $data['response']['code'], $data['response']['message'] ) );
			WP_CLI::line( wp_json_encode( json_decode( $data['body'] ), JSON_PRETTY_PRINT ) ?: '' );
		}
	}

	/**
	 * Filters out al non-integers and 0 values.
	 *
	 * @return int[]
	 */
	protected function filter_post_ids( string $comma_separated_post_ids ): array {
		$post_ids = array();

		foreach ( explode( ',', $comma_separated_post_ids ) as $arg ) {
			if ( (int) $arg !== 0 ) {
				$post_ids[] = (int) $arg;
			}
		}

		return $post_ids;
	}
}
