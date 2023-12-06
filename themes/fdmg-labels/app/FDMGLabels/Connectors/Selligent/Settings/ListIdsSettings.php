<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class ListIdsSettings {

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

	public function get_article(): int {
		return (int) ( $this->data[ Selligent::OPTION_LIST_ID_ARTICLE ] ?? 0 );
	}

	public function get_newsletter(): int {
		return (int) ( $this->data[ Selligent::OPTION_LIST_ID_NEWSLETTER ] ?? 0 );
	}
}
