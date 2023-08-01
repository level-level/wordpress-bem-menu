<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class Settings {

	protected static ?Settings $instance;

	/**
	 * ACF group fields data
	 *
	 * @var array<string, mixed>
	 */
	protected array $data;

	/**
	 * @param array<string,mixed> $data
	 */
	protected function __construct( array $data ) {
		$this->data = $data;
	}

	public static function get_instance(): self {
		static $instance = null;

		if ( null === $instance ) {
			$acf_options = get_field( Selligent::OPTION_GROUP, 'options' );
			$instance    = new self( is_array( $acf_options ) ? $acf_options : array() );
		}

		return $instance;
	}

	public function get_base_url(): string {
		return (string) ( $this->data[ Selligent::OPTION_BASE_URL ] ?? '' );
	}

	public function get_username(): string {
		return (string) ( $this->data[ Selligent::OPTION_USERNAME ] ?? '' );
	}

	public function get_password(): string {
		return (string) ( $this->data[ Selligent::OPTION_PASSWORD ] ?? '' );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function get_gates(): array {
		return $this->data[ Selligent::OPTION_GATES ] ?? array();
	}

	public function get_send_newsletter_gate(): string {
		return (string) ( $this->get_gates()[ Selligent::OPTION_GATE_SEND_NEWSLETTER ] ?? '' );
	}

	public function get_optin_gate(): string {
		return (string) ( $this->get_gates()[ Selligent::OPTION_GATE_OPTIN ] ?? '' );
	}

	/**
	 * @return array<string,mixed>
	 */
	protected function get_list_ids(): array {
		return $this->data[ Selligent::OPTION_LIST_IDS ] ?? array();
	}

	public function get_article_list_id(): int {
		return (int) ( $this->get_list_ids()[ Selligent::OPTION_LIST_ID_ARTICLE ] ?? 0 );
	}

	public function get_newsletter_list_id(): int {
		return (int) ( $this->get_list_ids()[ Selligent::OPTION_LIST_ID_NEWSLETTER ] ?? 0 );
	}
}
