<?php

namespace FDMGLabels\Connectors\FDMGAPI;

class Configuration {

	public const ENVIRONMENTS = array(
		'DEV'  => array(
			'base_url' => 'https://api-dev.fdmg.org',
			'oauth_base_url' => 'https://login-dev.fdmg.nl',
		),
		'ACC'  => array(
			'base_url' => 'https://api-acc.fdmg.org',
			'oauth_base_url'=> 'https://login-acc.fdmg.nl',
		),
		'PROD'  => array(
			'base_url' => 'https://api.fdmg.org',
			'oauth_base_url' => 'https://login.fdmg.nl',
		),
	);

	public const SITE_CONNECTIONS = array(
		'energeia',
		'pensioenpro',
	);

	/**
	 * Determines if all the required API settings are configured.
	 */
	public function is_configured(): bool {
		return ! empty( $this->get_base_url() )
			&& ! empty( $this->get_oauth_base_url() )
			&& ! empty( $this->get_oauth_client_id() )
			&& ! empty( $this->get_oauth_client_secret() );
	}

	public function get_base_url(): string {
		return defined( 'FDMG_API_BASE_URL' ) ? constant( 'FDMG_API_BASE_URL' ) : '';
	}

	public function get_oauth_base_url(): string {
		return defined( 'FDMG_API_OAUTH_BASE_URL' ) ? constant( 'FDMG_API_OAUTH_BASE_URL' ) : '';
	}

	public function get_oauth_client_id(): string {
		return defined( 'FDMG_API_OAUTH_CLIENT_ID' ) ? constant( 'FDMG_API_OAUTH_CLIENT_ID' ) : '';
	}

	public function get_oauth_client_secret(): string {
		return defined( 'FDMG_API_OAUTH_CLIENT_SECRET' ) ? constant( 'FDMG_API_OAUTH_CLIENT_SECRET' ) : '';
	}
}
