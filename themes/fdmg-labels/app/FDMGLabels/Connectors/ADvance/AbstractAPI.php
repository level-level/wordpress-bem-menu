<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI {
	protected ClientInterface $client;
	protected int $version = 1;

	public function __construct( ClientInterface $client ) {
		$this->client = $client;
	}

	public function auto_login( string $frontend_cookie, string $concurrency_cookie, ?string $ip_address ): ResponseInterface {
		return $this->client->request(
			'POST',
			'/Login/AutoLogin/1/',
			array(
				RequestOptions::FORM_PARAMS => array(
					'frontend_cookie'    => $frontend_cookie,
					'concurrency_cookie' => $concurrency_cookie,
					'ip_address'         => $ip_address,
				),
			)
		);
	}
}
