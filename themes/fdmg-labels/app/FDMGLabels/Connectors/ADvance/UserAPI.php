<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\RequestOptions;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;

class UserAPI extends AbstractAPI {

	/**
	 * Retrieves subscriptions of a user.
	 *
	 * @param string|null $user_token User token or party ID is required.
	 * @param string|null $party_id User token or party ID is required.
	 * @param string|null $ip_address
	 * @throws InvalidArgumentException If both $user_token and $party_id are null.
	 * @return ResponseInterface
	 */
	public function get_user_subscriptions( ?string $user_token, ?string $party_id, ?string $ip_address ): ResponseInterface {
		if ( $user_token === null && $party_id === null ) {
			throw new InvalidArgumentException( 'Either $user_token or $party_id must be provided.' );
		}

		$params = array();
		if ( $user_token !== null ) {
			$params['userToken'] = $user_token;
		}
		if ( $party_id !== null ) {
			$params['partyId'] = $party_id;
		}
		if ( $ip_address !== null ) {
			$params['ipAddress'] = $ip_address;
		}

		$response = $this->client->request(
			'POST',
			'/User/GetUserSubscriptions/1/',
			array(
				RequestOptions::FORM_PARAMS => $params,
			)
		);

		return $response;
	}
}
