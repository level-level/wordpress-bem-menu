<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use Monolog\Registry;
use WP_Error;

class Client {
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
	public function register_post( base_object $post, ii_newsletter $newsletter ) {
		return $this->post( $this->get_list_path( $this->settings->get_list_ids()->get_article() ), ( new DataGenerator() )->get_article_data( $post, $newsletter ) );
	}

	/**
	 * Registers a new newsletter.
	 *
	 * @param ii_newsletter $newsletter
	 * @param Newsletter::META_PREVIEW_RECIPIENTS_OPTION_* $test_mode
	 * @return array|WP_Error
	 */
	public function register_newsletter( ii_newsletter $newsletter, string $test_mode ) {
		return $this->post( $this->get_list_path( $this->settings->get_list_ids()->get_newsletter() ), ( new DataGenerator() )->get_newsletter_data( $newsletter, $test_mode ) );
	}

	/**
	 * Sends the most recent registered newsletter of specific publication and type to all recipients.
	 * Most recent newsletter needs to be provided to determine the publication and type.
	 *
	 * @return array|WP_Error
	 */
	public function send_newsletter( ii_newsletter $newsletter ) {
		$endpoint = 'campaigns/triggerbyjsonwithresult';
		$data     = array(
			'Gate'      => $this->settings->get_gates()->get_send_newsletter(),
			'Gateinput' => array(
				'PUBLICATION' => $newsletter->get_publication(),
				'TYPE'        => $newsletter->get_type(),
			),
		);

		return $this->post( $endpoint, $data );
	}

	/**
	 * Subscribes the provided e-mail address to the newsletter.
	 *
	 * @return array|WP_Error
	 */
	public function optin_email( string $source, string $email, bool $commercial, bool $research ) {
		$endpoint = 'campaigns/triggerbyjsonwithresult';
		$data     = array(
			'Gate'      => $this->settings->get_gates()->get_optin(),
			'Gateinput' => array(
				'MAIL'              => $email,
				'SOURCE'            => $source,
				'OPTIN_NIEUWSBRIEF' => '1', // Always true
				'OPTIN_COMMERCIEEL' => $commercial ? '1' : '0',
				'OPTIN_ONDERZOEK'   => $research ? '1' : '0',
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
				'body'    => strval( wp_json_encode( $data ) ),
				'timeout' => 20,
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
}
