<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class NewsletterPublicationSettings {

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

	public function get_name(): string {
		return (string) ( $this->data[ Selligent::OPTION_NEWSLETTER_PUBLICATION_NAME ] ?? '' );
	}

}
