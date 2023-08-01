<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use Monolog\Registry;
use WP_Error;

class Client {
	/**
	 * Defines the platform for Selligent
	 *
	 * @var string
	 */
	public const PUBLICATION = 'IMPACT_INVESTOR';

	public const BASE_PATH = '/restapi/api/sync/';

	protected string $base_url;

	protected string $user;

	protected string $pass;

	protected Settings $settings;

	/**
	 * @psalm-suppress RedundantCast
	 */
	public function __construct() {
		$this->settings = Settings::get_instance();
	}

	/**
	 * Registers a post to Selligent.
	 *
	 * @return array|WP_Error
	 */
	public function register_post( base_object $post ) {
		return $this->post( $this->get_list_path( $this->settings->get_article_list_id() ), ( new DataGenerator() )->get_article_data( $post ) );
	}

	/**
	 * Registers a new newsletter.
	 *
	 * @param ii_newsletter $newsletter
	 * @param bool $test_mode
	 * @return array|WP_Error
	 */
	public function register_newsletter( ii_newsletter $newsletter, bool $test_mode ) {
		return $this->post( $this->get_list_path( $this->settings->get_newsletter_list_id() ), ( new DataGenerator() )->get_newsletter_data( $newsletter, $test_mode ) );
	}

	/**
	 * Sends the most recent registered newsletter to all recipients.
	 *
	 * @return array|WP_Error
	 */
	public function send_newsletter() {
		$endpoint = 'campaigns/triggerbyjsonwithresult';
		$data     = array(
			'Gate'      => $this->settings->get_send_newsletter_gate(),
			'Gateinput' => array(
				'PUBLICATION' => self::PUBLICATION,
				'TYPE'        => self::get_type(),
			),
		);

		return $this->post( $endpoint, $data );
	}

	/**
	 * Subscribes the provided e-mail address to the newsletter.
	 *
	 * @return array|WP_Error
	 */
	public function optin_email( string $email ) {
		$endpoint = 'campaigns/triggerbyjsonwithresult';
		$data     = array(
			'Gate'      => $this->settings->get_optin_gate(),
			'Gateinput' => array(
				'MAIL' => $email,
			),
		);

		return $this->post( $endpoint, $data );
	}

	/**
	 * Returns the path of a list that is used to update either an article or a
	 * newsletter.
	 *
	 * @param int $list_id
	 */
	public function get_list_path( int $list_id ): string {
		return sprintf( 'lists/%d/articles', $list_id );
	}

	/**
	 * Perform a POST request to Selligent
	 *
	 * @param string $url  Endpoint to POST to, BASE_URL and BASE_PATH will be
	 * prefixed to this value.
	 * @param array $data Associative array of data that is sent in the body.
	 * @return array|WP_Error
	 */
	public function post( string $path, array $data ) {
		$url      = untrailingslashit( $this->settings->get_base_url() ) . self::BASE_PATH . $path;
		$response = wp_remote_post(
			$url, array(
				'headers' => $this->get_auth_headers( self::BASE_PATH . $path ),
				'body'    => wp_json_encode( $data ),
				'timeout' => 10,
			)
		);

		if ( $response instanceof WP_Error ) {
			Registry::wordpress()->warning(
				'Unexpected error occured in Selligent request.', array(
					'wp_error'     => $response,
					'request_url'  => $url,
					'request_body' => wp_json_encode( $data ),
				)
			);
			return $response;
		}

		if ( $response['response']['code'] < 200 || $response['response']['code'] >= 400 ) {
			$extra_data = array(
				'response_code'    => $response['response']['code'],
				'response_message' => $response['response']['message'],
				'response_body'    => $response['body'],
				'request_url'      => $url,
				'request_body'     => wp_json_encode( $data ),
			);
			Registry::wordpress()->warning( 'Unexpected status code returned in Selligent request.', $extra_data );
			return new WP_Error( $response['response']['code'], $response['response']['message'], $extra_data );
		}

		return $response;
	}

	/**
	 * Authorization headers are generated on the fly based on the endpoint and
	 * the current timestamp.
	 *
	 * @param string $endpoint Full endpoint without BASE_URL.
	 */
	protected function get_auth_headers( string $endpoint ): array {
		$timestamp = time();
		$auth_hash = hash_hmac( 'sha256', sprintf( '%s-POST-%s', $timestamp, $endpoint ), $this->settings->get_password() );

		return array(
			'Authorization' => sprintf( 'hmac %s:%s:%s', $this->settings->get_username(), $auth_hash, $timestamp ),
			'Content-Type'  => 'application/json',
		);
	}

	/**
	 * The mailing list type. This type determines the mailing list that will
	 * be used. The production mailing list is 'MVP'. Custom test lists can be
	 * created if needed.
	 */
	public static function get_type(): string {
		return defined( 'II_SELLIGENT_TYPE' ) ? constant( 'II_SELLIGENT_TYPE' ) : 'NONE';
	}
}
