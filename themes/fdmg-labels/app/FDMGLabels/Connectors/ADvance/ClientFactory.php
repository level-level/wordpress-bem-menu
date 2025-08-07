<?php

namespace FDMGLabels\Connectors\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\MessageFormatter;
use GuzzleHttp\Middleware;
use Monolog\Registry;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;

class ClientFactory {

	private const REQUEST_TIMEOUT = 10;

	public function get_client(): ClientInterface {
		$stack = HandlerStack::create();

		// Add a logger to the stack
		$stack->push(
			Middleware::log(
				Registry::wordpress(),
				new MessageFormatter( '{request} - {response}' ),
				LogLevel::DEBUG
			), 'logger'
		);

		// Add request ID
		$stack->push(
			Middleware::mapRequest(
				function ( RequestInterface $r ) {
					return $r->withHeader( 'X-Request-ID', Uuid::uuid7()->toString() );
				}
			), 'request_id'
		);

		// Add response format
		$stack->push(
			Middleware::mapRequest(
				function ( RequestInterface $r ) {
					return $r->withHeader( 'ADAPI_RESPONSE_FORMAT', 'json' );
				}
			), 'response_format'
		);

		$configuration = get_field( ADvance::OPTION_GROUP, 'option' ) ?: array();
		$base_url      = (string) ( $configuration[ ADvance::OPTION_API_BASE_URL ] ?? '' );
		$access_token  = (string) ( $configuration[ ADvance::OPTION_API_ACCESS_TOKEN ] ?? '' );
		$access_hash   = (string) ( $configuration[ ADvance::OPTION_API_ACCESS_HASH ] ?? '' );

		// Add authentication
		$stack->push(
			Middleware::mapRequest(
				function ( RequestInterface $r ) use ( $access_token, $access_hash ) {
					return $r->withHeader( 'ADAPI_ACCESS_TOKEN', $access_token )
						->withHeader( 'DAPI_ACCESS_HASH', $access_hash );
				}
			), 'auth'
		);

		return new GuzzleClient(
			array(
				'base_uri'        => $base_url,
				'connect_timeout' => self::REQUEST_TIMEOUT,
				'read_timeout'    => self::REQUEST_TIMEOUT,
				'timeout'         => self::REQUEST_TIMEOUT,
				'handler'         => $stack,
			)
		);
	}
}
