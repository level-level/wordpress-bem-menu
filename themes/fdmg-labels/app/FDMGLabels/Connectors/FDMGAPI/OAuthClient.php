<?php

namespace FDMGLabels\Connectors\FDMGAPI;

use Exception;
use FDMGLabels\Connectors\FDMGAPI\Objects\OAuthTokenResponse;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\RequestOptions;

class OAuthClient {
	public const TOKEN_PATH = '/auth/realms/services/protocol/openid-connect/token';

	public function __construct(
		protected GuzzleClient $client,
		protected Configuration $configuration,
	) {}

	/**
	 * @throws Exception
	 */
	public function get_token(): OAuthTokenResponse {
		try {
			$response = $this->client->post(
				$this->configuration->get_oauth_base_url() . self::TOKEN_PATH,
				array(
					RequestOptions::FORM_PARAMS => array(
						'client_id'     => $this->configuration->get_oauth_client_id(),
						'client_secret' => $this->configuration->get_oauth_client_secret(),
						'grant_type'    => 'client_credentials',
					),
				)
			);
		} catch ( Exception $e ) {
			throw $e;
		}

		$decoded_json = json_decode( $response->getBody(), true, 512, JSON_THROW_ON_ERROR );
		if ( ! is_array( $decoded_json ) ) {
			throw new Exception( 'Invalid response from API' );
		}

		/**
		 * Force type the array to be of this type.
		 *
		 * @var array{
		 *  'access_token': string,
		 *  'expires_in': int,
		 *  'refresh_expires_in': int,
		 *  'token_type': string,
		 *  'not-before-policy': int,
		 *  'scope': string
		 * } $decoded_json
		 */
		return OAuthTokenResponse::from_data( $decoded_json );
	}
}
