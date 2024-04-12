<?php

namespace FDMGLabels\Connectors\FDMGAPI;

use Clarkson\Theme\ACFPages\FDMGAPI as ACFPagesFDMGAPI;

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
			&& ! empty( $this->get_oauth_client_secret() )
			&& ! empty( $this->get_site_connection() );
	}

	public function get_base_url(): string {
		$environment = $this->get_environment();
		return isset( self::ENVIRONMENTS[ $environment ] ) ? self::ENVIRONMENTS[ $environment ]['base_url'] : '';
	}

	public function get_oauth_base_url(): string {
		$environment = $this->get_environment();
		return isset( self::ENVIRONMENTS[ $environment ] ) ? self::ENVIRONMENTS[ $environment ]['oauth_base_url'] : '';
	}

	public function get_oauth_client_id(): string {
		return (string) get_option( sprintf( 'options_%s_%s', ACFPagesFDMGAPI::OPTION_GROUP, ACFPagesFDMGAPI::OPTION_CLIENT_ID ) );
	}

	public function get_oauth_client_secret(): string {
		return (string) get_option( sprintf( 'options_%s_%s', ACFPagesFDMGAPI::OPTION_GROUP, ACFPagesFDMGAPI::OPTION_CLIENT_SECRET ) );
	}

	public function get_site_connection(): string {
		return (string) get_option( sprintf( 'options_%s_%s', ACFPagesFDMGAPI::OPTION_GROUP, ACFPagesFDMGAPI::OPTION_SITE_CONNECTION ) );
	}

	public function get_environment(): string {
		return (string) get_option( sprintf( 'options_%s_%s', ACFPagesFDMGAPI::OPTION_GROUP, ACFPagesFDMGAPI::OPTION_ENVIRONMENT ) );
	}
}
