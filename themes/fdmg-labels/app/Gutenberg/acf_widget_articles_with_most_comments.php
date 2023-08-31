<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\LatestArticles;
use Clarkson_Core\WordPress_Object\post;

class acf_widget_articles_with_most_comments extends base_block {

	/**
	* Get posts
	* @return array<int, \Clarkson_Core\WordPress_Object\Clarkson_Object>
	*/
	public function get_articles_sorted_by_comment_count(): array {
		$amount = $this->content_attributes[ LatestArticles::WIDGET_META_AMOUNT ] ?? 5;

		$args = array(
			'posts_per_page' => $amount,
			'orderby'        => 'comment_count',
			'order'            => 'DESC',
		);

		return post::get_many( $args );
	}
}
