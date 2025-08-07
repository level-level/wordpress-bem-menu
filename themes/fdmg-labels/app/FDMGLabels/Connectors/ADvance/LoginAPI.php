<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

class LoginAPI extends AbstractAPI {

	public function auto_login( string $frontend_cookie, string $concurrency_cookie, ?string $ip_address ): ResponseInterface {
		$params = array(
			'frontend_cookie'    => $frontend_cookie,
			'concurrency_cookie' => $concurrency_cookie,
		);
		if ( $ip_address !== null ) {
			$params['ip_address'] = $ip_address;
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
}
