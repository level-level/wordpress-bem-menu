<?php

namespace FDMGLabels\CLI;

use Exception;
use FDMGLabels\Connectors\FDMGAPI\Configuration;
use FDMGLabels\Connectors\FDMGAPI\GuzzleClientFactory;
use FDMGLabels\Connectors\FDMGAPI\SAMLClient;
use Monolog\Registry;
use WP_CLI;

class FDMGAPI {

	/**
	 * Get SAML Redirect IP ranges
	 *
	 * @subcommand get-saml-redirect-ip-ranges
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels fdmg-api get-saml-redirect-ip-ranges
	 */
	public function get_saml_redirect_ip_ranges( array $args, array $assoc_args ): void {
		$config = new Configuration();
		if ( ! $config->is_configured() ) {
			WP_CLI::error( 'FDMG API is not configured' );
		}

		$guzzle_client_factory = ( new GuzzleClientFactory( $config, Registry::wordpress() ) );
		try {
			$saml     = new SAMLClient( $guzzle_client_factory->get_client(), $config );
			$response = $saml->get_redirect_ip_ranges();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			WP_CLI::error( sprintf( '%s: %s', $e->getCode(), $e->getMessage() ) );
			exit;
		}

		WP_CLI::line( wp_json_encode( $response, JSON_PRETTY_PRINT ) ?: '' );
	}
}
