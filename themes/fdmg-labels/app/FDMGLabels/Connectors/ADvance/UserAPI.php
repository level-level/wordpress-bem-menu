<?php

namespace FDMGLabels\Connectors\ADvance;

use DateTimeImmutable;
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
	 * @param string $email_address User emailaddress for which to update subscriptions.
	 * @param array<string> $newsletter_codes Array of newsletter codes to activate or deactivate.
	 * @param bool $activate True to activate, false to deactivate.
	 * @param bool $create_party Whether to create a party if it does not exist. OPTIONAL.
	 * @param DateTimeImmutable $response_date_time Optional date time string for the response. OPTIONAL.
	 * @param string $request_source Optional request source. OPTIONAL.
	 * @return ResponseInterface
	 */
	public function update_newsletter_subscription_by_email_address( string $email_address, array $newsletter_codes, bool $activate = true, bool $create_party = true, ?DateTimeImmutable $response_date_time = null, ?string $request_source = null ): ResponseInterface {
		if ( ! $email_address ) {
			throw new InvalidArgumentException( 'Email address must be provided.' );
		}

		$params                 = array();
		$params['emailAddress'] = $email_address;

		if ( $create_party ) {
			$params['createParty'] = 'true'; // Booleans must be passed as a string
		}

		if ( $response_date_time ) {
			$params['responseDateTime'] = $response_date_time->format( 'Y-m-d H:i:s' ); // FORMAT: yyyy-MM-dd hh:mm:ss
		}

		$newsletter_codes = implode( ',', $newsletter_codes );

		// One of these two parameters is required.
		if ( $activate ) {
			$params['activateNewsletterCode_CSV'] = $newsletter_codes;
		} else {
			$params['inactivateNewsletterCode_CSV'] = $newsletter_codes;
		}

		if ( $request_source ) {
			$params['requestSource'] = $request_source;
		}

		$response = $this->client->request(
			'POST',
			'/User/UpdateNewsletterSubscriptionsByEmailAddress/1/',
			array(
				RequestOptions::FORM_PARAMS => $params,
			)
		);

		return $response;
	}
}
