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

	/**
	 * Activates newsletter subscriptions for a user.
	 *
	 * @param string|null $user_token User token or party ID is required.
	 * @param string|null $party_id User token or party ID is required.
	 * @param array<int> $newsletter_ids Array of newsletter IDs to activate or deactivate.
	 * @param bool $activate True to activate, false to deactivate.
	 * @param string|null $response_date_time OPTIONAL - date to which the preference should be attributed. Format: yyyy-MM-dd hh:mm:ss
	 * @throws InvalidArgumentException If both $user_token and $party_id are null.
	 * @return ResponseInterface
	 */
	public function update_newsletter_subscription( ?string $user_token, ?string $party_id, array $newsletter_ids, bool $activate = true, ?string $response_date_time ): ResponseInterface {
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

		// One of these two parameters is required.
		if ( $activate ) {
			$params['activateNewsletterCode_CSV'] = implode( ',', $newsletter_ids );
		} else {
			$params['inactivateNewsletterCode_CSV'] = implode( ',', $newsletter_ids );
		}

		if( $response_date_time !== null ) {
			$params['responseDateTime'] = $response_date_time;
		}

		$response = $this->client->request(
			'POST',
			'/User/UpdateNewsletterSubscription/1/',
			array(
				RequestOptions::FORM_PARAMS => $params,
			)
		);

		return $response;
	}
}
