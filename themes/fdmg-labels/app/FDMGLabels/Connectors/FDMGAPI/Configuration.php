<?php

namespace FDMGLables\Connectors\FDMGAPI;

class Configuration {

	public function is_configured(): bool {
		return ! empty( $this->get_base_url() )
			&& ! empty( $this->get_client_id() )
			&& ! empty( $this->get_client_secret() );
	}

	public function get_base_url(): string {
		return defined( 'FDMG_API_BASE_URL' ) ? constant( 'FDMG_API_BASE_URL' ) : '';
	}

	public function get_client_id(): string {
		return defined( 'FDMG_API_CLIENT_ID' ) ? constant( 'FDMG_API_CLIENT_ID' ) : '';
	}

	public function get_client_secret(): string {
		return defined( 'FDMG_API_CLIENT_SECRET' ) ? constant( 'FDMG_API_CLIENT_SECRET' ) : '';
	}
}
