<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\ArticlesByAuthor;
use Clarkson_Core\WordPress_Object\post;

class acf_widget_articles_by_author extends base_block {

	/**
	 * @return array<int, \Clarkson_Core\WordPress_Object\Clarkson_Object>
	 */
	public function get_articles_by_author(): array {
		$amount = $this->content_attributes[ ArticlesByAuthor::WIDGET_META_AMOUNT ] ?? 5;
		$post   = $this->get_object();

		if ( ! $post instanceof post ) {
			return array();
		}

		$author = $post->get_authors();

		if ( ! $author ) {
			return array();
		}

		$author_id = $author[0]->get_id();

		$args = array(
			'posts_per_page' => $amount,
			'meta_query'     => array(
				array(
					'key'     => 'authors',
					'value'   => $author_id,
					'compare' => 'LIKE',
				),
			),
		);

		return post::get_many( $args );
	}
}
