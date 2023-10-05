<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;

class ArticleGroup {

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
	 * Get the articles that should be displayed in the newsletter
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_articles(): array {
		$articles = $this->data[ Newsletter::META_ARTICLE_GROUP_ARTICLES ] ?? array();
		return is_array( $articles ) ? $articles : array();
	}

	/**
	 * Get the articles that should be displayed in the newsletter
	 *
	 * @return int[]
	 */
	public function get_article_ids(): array {
		$articles = $this->get_articles();

		$article_ids = array_map(
			function( array $articles_row ): int {
				return (int) ( $articles_row[ Newsletter::META_ARTICLE_GROUP_ARTICLE_ARTICLE ] ?? 0 );
			}, $articles
		);

		return array_filter( $article_ids );
	}

}
