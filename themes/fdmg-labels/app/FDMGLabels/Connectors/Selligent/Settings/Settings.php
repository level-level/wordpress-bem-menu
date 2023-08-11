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

	protected function __construct() {
		$this->clear_cache();
	}

	public static function get_instance(): self {
		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function clear_cache(): void {
		$acf_options = get_field( Selligent::OPTION_GROUP, 'options' );
		$this->data  = is_array( $acf_options ) ? $acf_options : array();
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

	public function get_gates(): GatesSettings {
		return new GatesSettings( $this->data[ Selligent::OPTION_GATES ] ?? array() );
	}

	public function get_list_ids(): ListIdsSettings {
		return new ListIdsSettings( $this->data[ Selligent::OPTION_LIST_IDS ] ?? array() );
	}

	public function get_newsletter(): NewsletterSettings {
		return new NewsletterSettings( $this->data[ Selligent::OPTION_NEWSLETTER ] ?? array() );
	}
}
