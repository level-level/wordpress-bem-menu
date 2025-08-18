<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class LoginAPI extends AbstractAPI {

	public function auto_login( string $frontend_cookie, string $concurrency_cookie, ?string $ip_address ): ResponseInterface {
		$params = array(
			'frontendCookie'    => $frontend_cookie,
			'concurrencyCookie' => $concurrency_cookie,
		);
		if ( $ip_address !== null ) {
			$params['ipAddress'] = $ip_address;
		}

		$response = $this->client->request(
			'POST',
			'/Login/AutoLogin/1/',
			array(
				RequestOptions::FORM_PARAMS => $params,
			)
		);

		return $response;
	}

	/**
	 * Parses the response from the API into a JSON array.
	 *
	 * @param ResponseInterface $response
	 * @return array<string,mixed>
	 */
	public function parse_response_to_json( ResponseInterface $response ): array {
		$body = (string) $response->getBody();
		$body = trim( $body, "\xEF\xBB\xBF" ); // Remove BOM (Byte Order Mark) because we don't get valid json back.
		$body = trim( $body );
		return json_decode( $body, true, 512, JSON_THROW_ON_ERROR );
	}
}
