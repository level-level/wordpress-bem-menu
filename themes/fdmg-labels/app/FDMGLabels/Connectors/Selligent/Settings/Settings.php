<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class Settings {

	protected static ?Settings $instance;

	protected array $data;

	protected function __construct( array $data ) {
		$this->data = $data;
	}

	public static function get_instance(): self {
		static $instance = null;

		if ( null === $instance ) {
			$acf_options = get_field( Selligent::OPTION_GROUP, 'options' );
			$instance = new self( is_array( $acf_options ) ? $acf_options : array() );
		}

		return $instance;
	}

	public function get_base_url(): string {
		return $this->data[ Selligent::OPTION_BASE_URL ] ?? '';
	}

	public function get_username(): string {
		return $this->data[ Selligent::OPTION_USERNAME ] ?? '';
	}

	public function get_password(): string {
		return $this->data[ Selligent::OPTION_PASSWORD ] ?? '';
	}
}
