<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class GatesSettings {

	/**
	 * ACF group fields data
	 *
	 * @var array<string, mixed>
	 */
	protected array $data;

	/**
	 * @param array<string,mixed> $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	public function get_send_newsletter(): string {
		return (string) ( $this->data[ Selligent::OPTION_GATE_SEND_NEWSLETTER ] ?? '' );
	}

	public function get_optin(): string {
		return (string) ( $this->data[ Selligent::OPTION_GATE_OPTIN ] ?? '' );
	}
}
