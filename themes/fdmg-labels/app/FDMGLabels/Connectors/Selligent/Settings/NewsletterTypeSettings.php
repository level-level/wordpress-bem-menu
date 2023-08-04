<?php

namespace FDMGLabels\Connectors\Selligent\Settings;

use Clarkson\Theme\ACFPages\Selligent;

class NewsletterTypeSettings {

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

	public function get_type(): string {
		return (string) ( $this->data[ Selligent::OPTION_NEWSLETTER_TYPE_ARTICLE_GROUPS ] ?? '' );
	}

	/**
	 * @return array<NewsletterTypeArticleGroupSettings>
	 */
	public function get_article_groups(): array {
		$article_groups = $this->data[ Selligent::OPTION_NEWSLETTER_TYPE_ARTICLE_GROUPS ] ?? array();
		$article_groups = is_array( $article_groups ) ? $article_groups : array();
		return array_map(
			function( array $article_group_data ): NewsletterTypeArticleGroupSettings {
				return new NewsletterTypeArticleGroupSettings( $article_group_data );
			}, $article_groups
		);
	}

}
