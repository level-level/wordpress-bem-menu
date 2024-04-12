<?php

namespace FDMGLables\Connectors\FDMGAPI;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Ramsey\Uuid\Uuid;

class GuzzleClientFactory {
	private const CLIENT_TIMEOUT         = 10;
	private const ACCESS_TOKEN_TRANSIENT = 'fdmg_api_access_token';

	public function __construct(
		protected Configuration $configuration,
		protected LoggerInterface $logger,
	) { }

	public function get_client(): GuzzleClient {
		if ( ! $this->configuration->is_configured() ) {
			throw new \Exception( 'FDMG API is not configured' );
		}

		$stack = HandlerStack::create();

		$stack->push(
			Middleware::log(
				$this->logger,
				new \GuzzleHttp\MessageFormatter( '{request} - {response}' ),
				LogLevel::DEBUG
			)
		);

		$stack->push(
			Middleware::mapRequest(
				function ( RequestInterface $r ) {
					return $r->withHeader( 'X-Request-ID', Uuid::uuid7()->toString() );
				}
			), 'request_id'
		);

		$stack = $this->add_automatic_token_refresh_to_stack( $stack );

		return new GuzzleClient(
			array(
				'base_uri' => trailingslashit( $this->configuration->get_base_url() ),
				'timeout'  => self::CLIENT_TIMEOUT,
				'handler'  => $stack,
			)
		);
	}

	/**
	 * Force a refresh of the access token
	 */
	protected function refresh_access_token(): void {
		$this->get_access_token( false );
	}

	/**
	 * Get the acces token, if it has expired, get a new one.
	 *
	 * @param bool $use_transient Whether to use the transient or force a new
	 * token to be fetched.
	 */
	protected function get_access_token( bool $use_transient = true ): ?string {
		$token = get_transient( self::ACCESS_TOKEN_TRANSIENT );

		if ( $token && $use_transient ) {
			return $token;
		}

		$client = new OAuthClient( $this->get_client(), $this->configuration );

		try {
			$token = $client->get_token();
		} catch ( Exception $e ) {
			return null;
		}

		set_transient( self::ACCESS_TOKEN_TRANSIENT, $token['access_token'], $token['expires_in'] );

		return $token['access_token'];
	}

	/**
	 * Add automatic token refresh to the client, which will both add the access
	 * token on all POST requests and refresh the token if it has expired.
	 */
	protected function add_automatic_token_refresh_to_stack( HandlerStack $stack ): HandlerStack {
		$stack->push(
			Middleware::retry(
				/**
				 * If a request fails with a status code 401 or 403, retry once with a new access token.
				 */
				function (
					$retries,
					\GuzzleHttp\Psr7\Request $request,
					\GuzzleHttp\Psr7\Response $response = null,
					\Throwable $exception = null
				) {
					if ( $retries >= 1 || $request->getUri()->getPath() === OAuthClient::TOKEN_PATH ) {
						return false;
					}

					if ( $response && in_array( $response->getStatusCode(), array( 401, 403 ), true ) ) {
						$this->refresh_access_token();
						return true;
					}

					return false;
				}
			)
		);

		$stack->push(
			Middleware::mapRequest(
				function ( RequestInterface $request ) {
					if ( $request->getUri()->getPath() !== OAuthClient::TOKEN_PATH ) {
						return $request->withHeader( 'Authorization', 'Bearer ' . $this->get_access_token() );
					}
					return $request;
				}
			)
		);

		return $stack;
	}
}
