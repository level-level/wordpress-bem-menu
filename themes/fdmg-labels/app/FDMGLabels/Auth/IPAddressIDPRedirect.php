<?php

namespace FDMGLabels\Auth;

use Clarkson\Utilities\IPAddress;
use Exception;
use FDMGLabels\Connectors\FDMGAPI\Configuration;
use FDMGLabels\Connectors\FDMGAPI\GuzzleClientFactory;
use FDMGLabels\Connectors\FDMGAPI\Objects\SAMLRedirectIPRange;
use FDMGLabels\Connectors\FDMGAPI\SAMLClient;
use Monolog\Registry;
use Psr\Log\NullLogger;

class IPAddressIDPRedirect {

	protected const REDIRECT_IP_RANGES_TRANSIENT = 'fdmg_labels_redirect_ip_ranges';

	/**
	 * Attempts to find the IDP alias for the provided IP address.
	 * The IDP alias can be used to redirect the user to the correct IDP to login.
	 */
	public function ip_address_to_idp_alias( string $ip_address, bool $from_transient = true ): ?string {
		$redirect_ip_ranges = $this->get_redirect_list( $from_transient );
		$ip_util            = new IPAddress();

		$config    = new Configuration();
		$idp_alias = null;
		foreach ( $redirect_ip_ranges as $redirect_ip_range ) {
			$site_connections       = $redirect_ip_range->get_clients();
			$site_connections_parts = explode( ',', $site_connections );
			$site_connections_parts = array_map( 'trim', $site_connections_parts );
			if ( $site_connections !== '*' && ! in_array( $config->get_site_connection(), $site_connections_parts, true ) ) {
				continue;
			}

			if ( ! $ip_util->is_ip_in_range( $ip_address, $redirect_ip_range->get_ip_start(), $redirect_ip_range->get_ip_end() ) ) {
				continue;
			}

			$idp_alias = $redirect_ip_range->get_idp_alias();
			break;
		}
		return $idp_alias;
	}

	/**
	 * @return array<SAMLRedirectIPRange>
	 */
	public function get_redirect_list( bool $from_transient ): array {
		if ( $from_transient ) {
			$redirect_ip_ranges = $this->get_redirect_ip_ranges_from_transient();
			if ( is_array( $redirect_ip_ranges ) ) {
				return $redirect_ip_ranges;
			}
		}

		$redirect_ip_ranges = $this->get_redirect_list_from_api();
		$this->save_redirect_ip_ranges_to_transient( $redirect_ip_ranges );
		return $redirect_ip_ranges;
	}

	/**
	 * @return array<SAMLRedirectIPRange>
	 */
	protected function get_redirect_list_from_api(): array {
		$config = new Configuration();
		try {
			$logger = Registry::wordpress();
		} catch ( Exception $e ) {
			$logger = new NullLogger();
		}
		$guzzle_client_factory = ( new GuzzleClientFactory( $config, $logger ) );
		try {
			$saml_client                 = new SAMLClient( $guzzle_client_factory->get_client(), $config );
			$redirect_ip_ranges_response = $saml_client->get_redirect_ip_ranges();
		} catch ( Exception $e ) {
			return array();
		}

		return $redirect_ip_ranges_response->get_content();
	}

	/**
	 * @return array<SAMLRedirectIPRange>
	 */
	protected function get_redirect_ip_ranges_from_transient(): ?array {
		$redirect_ip_ranges = get_transient( self::REDIRECT_IP_RANGES_TRANSIENT );
		$redirect_ip_ranges = $redirect_ip_ranges ? json_decode( $redirect_ip_ranges, true ) : null;
		if ( ! is_array( $redirect_ip_ranges ) ) {
			return null;
		}

		return array_map(
			function ( $redirect_ip_range ): SAMLRedirectIPRange {
				return SAMLRedirectIPRange::from_data( $redirect_ip_range );
			}, $redirect_ip_ranges
		);
	}

	/**
	 * @param array<SAMLRedirectIPRange> $redirect_ip_ranges
	 */
	protected function save_redirect_ip_ranges_to_transient( array $redirect_ip_ranges ): void {
		$redirect_ip_ranges = array_map(
			function ( SAMLRedirectIPRange $redirect_ip_range ): array {
				return $redirect_ip_range->to_data();
			}, $redirect_ip_ranges
		);

		set_transient( self::REDIRECT_IP_RANGES_TRANSIENT, wp_json_encode( $redirect_ip_ranges ), 10 * MINUTE_IN_SECONDS );
	}
}
