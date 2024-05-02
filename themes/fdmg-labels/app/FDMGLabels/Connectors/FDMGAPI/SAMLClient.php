<?php

namespace FDMGLabels\Connectors\FDMGAPI;

use Exception;
use FDMGLabels\Connectors\FDMGAPI\Objects\SAMLRedirectIPRangesResponse;
use GuzzleHttp\Client as GuzzleClient;

class SAMLClient {
	public function __construct(
		protected GuzzleClient $client,
		protected Configuration $configuration,
	) {}

	/**
	 * @throws Exception
	 */
	public function get_redirect_ip_ranges(): SAMLRedirectIPRangesResponse {
		try {
			$response = $this->client->get( 'private/fdmg/account/saml/redirectipranges' );
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
		 *  'status': integer,
		 *  'statusType': string,
		 *  'content': array<
		 *      array{
		 *          'idpAlias': string,
		 *          'ipStart': string,
		 *          'ipEnd': string,
		 *          'clients': string
		 *      }
		 *  >,
		 *  'errorType': string,
		 *  'message': string,
		 *  'details': string,
		 *  'errorCode': string,
		 *  'path': string,
		 *  'method': string
		 * } $decoded_json
		 */
		return SAMLRedirectIPRangesResponse::from_data( $decoded_json );
	}
}
