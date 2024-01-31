<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class NewsletterSettings {

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

	/**
	 * @return array<NewsletterPublicationSettings>
	 */
	public function get_publications(): array {
		$publications = $this->data[ Selligent::OPTION_NEWSLETTER_PUBLICATIONS ] ?? array();
		$publications = is_array( $publications ) ? $publications : array();
		return array_map(
			function ( array $publication_data ): NewsletterPublicationSettings {
				return new NewsletterPublicationSettings( $publication_data );
			}, $publications
		);
	}

	/**
	 * @return array<NewsletterTypeSettings>
	 */
	public function get_types(): array {
		$types = $this->data[ Selligent::OPTION_NEWSLETTER_TYPES ] ?? array();
		$types = is_array( $types ) ? $types : array();
		return array_map(
			function ( array $type_data ): NewsletterTypeSettings {
				return new NewsletterTypeSettings( $type_data );
			}, $types
		);
	}
}
