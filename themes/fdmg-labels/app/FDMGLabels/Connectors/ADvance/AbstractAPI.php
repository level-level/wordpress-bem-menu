<?php

namespace FDMGLabels\Connectors\ADvance;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

abstract class AbstractAPI {
	public function __construct( protected ClientInterface $client ) {}

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
