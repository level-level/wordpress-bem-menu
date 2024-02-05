<?php

namespace FDMGLabels\Commands;

use Exception;
use FDMGLabels\Connectors\Selligent\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\NullLogger;
use WP_Error;

class OptinEmailForNewsletter implements CommandInterface, LoggerAwareInterface {

	use LoggerAwareTrait;

	protected string $source;
	protected string $email;
	protected bool $optin_commercial;
	protected bool $optin_research;

	public function __construct(
		string $source,
		string $email,
		bool $optin_commercial,
		bool $optin_research
	) {
		$this->logger           = new NullLogger();
		$this->email            = $email;
		$this->optin_commercial = $optin_commercial;
		$this->optin_research   = $optin_research;
	}

	/**
	 * Opts in an email for the newsletter
	 * @return array<string,mixed>|WP_Error
	 * @throws Exception
	 */
	public function execute(): array|WP_Error {
		$client   = new Client();
		$response = $client->optin_email( $this->source, $this->email, $this->optin_commercial, $this->optin_research );
		if ( $response instanceof WP_Error ) {
			$this->logger?->error(
				'Error while optin email for newsletter',
				array(
					'email'            => $this->email,
					'optin_commercial' => $this->optin_commercial,
					'optin_research'   => $this->optin_research,
					'error'            => $response,
				)
			);

			throw new Exception( $response->get_error_message(), (int) $response->get_error_code() );
		}
		return $response;
	}
}
