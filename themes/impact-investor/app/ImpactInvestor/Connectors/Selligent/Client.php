<?php

namespace ImpactInvestor\Connectors\Selligent;

use Clarkson_Core\WordPress_Object\post;

class Client {
	/**
	 * Defines the platform for Selligent
	 *
	 * @var string
	 */
	public const PUBLICATION = 'IMPACT_INVESTOR';

	public const BASE_URL = 'https://messagent.fdmediagroep.nl/restapi/api/sync/';

	public const LIST_ID_ARTICLE    = 1980;
	public const LIST_ID_NEWSLETTER = 1979;

	/**
	 * @var string
	 */
	protected $user;

	/**
	 * @var string
	 */
	protected $pass;

	public function __construct() {
		$this->user = defined( 'II_SELLIGENT_USER' ) ? constant( 'II_SELLIGENT_USER' ) : '';
		$this->pass = defined( 'II_SELLIGENT_PASS' ) ? constant( 'II_SELLIGENT_PASS' ) : '';
	}

	/**
	 * Registers a post to Selligent.
	 */
	public function register_post( post $post ): array {
		return $this->post( $this->get_list_url( self::LIST_ID_ARTICLE ), DataGenerator::get_article_data( $post ) );
	}

	/**
	 * Registers a new newsletter based on a list of posts.
	 * 
	 * @param int[] $post_ids
	 */
	public function register_newsletter( array $post_ids ): array {
		return $this->post( $this->get_list_url( self::LIST_ID_NEWSLETTER ), DataGenerator::get_newsletter_data( $post_ids ) );
	}

	/**
	 * Returns the url of a list that is used to update either an article or a
	 * newsletter.
	 *
	 * @param Client::LIST_ID_* $list_id
	 */
	public function get_list_url( int $list_id ): string {
		return sprintf( '%slists/%d/articles', self::BASE_URL, $list_id );
	}

	/**
	 * @param string $url  Full url endpoint.
	 * @param string $data Associative array of data that is sent in the body.
	 */
	public function post( string $url, array $data ): array {
		$data = wp_remote_post( $url, array(
			'headers' => $this->get_auth_headers( $url ),
			'body'    => $data,
		) );

		return $data ?? array();
	}

	/**
	 * Authorization headers are generated on the fly based on the endpoint and
	 * the current timestamp.
	 * 
	 * @param string $endpoint Full endpoint url.
	 */
	protected function get_auth_headers( string $endpoint ): array {
		$timestamp = time();
		$auth_hash = hash_hmac( 'sha256', sprintf( '%s-POST-%s', $timestamp, $endpoint ), $this->pass );

		return array(
			sprintf( 'Authorization: hmac %s:%s:%s', $this->user, $auth_hash, $timestamp ),
			'Content-Type: application/json'
		);
	}

	/**
	 * The mailing list type. There are currently two:
	 * WEEKLY: The standard mailing list that sends the email to all subscribers.
	 * MVP: Default. The mailing list used to test the API.
	 */
	public static function get_type(): string {
		return defined( 'II_SELLIGENT_TYPE' ) ? constant( 'II_SELLIGENT_TYPE' ) : 'MVP';
	}
}
