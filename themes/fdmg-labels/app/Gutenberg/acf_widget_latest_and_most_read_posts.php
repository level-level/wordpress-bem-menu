<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\LatestAndMostReadPosts;
use Clarkson_Core\WordPress_Object\post;
use FDMGLabels\KokoAnalytics\KokoAnalytics;

class acf_widget_latest_and_most_read_posts extends base_block {

	/**
	* Get latest posts
	*/
	public function get_latest_articles(): array {
		$amount = $this->content_attributes[ LatestAndMostReadPosts::BLOCK_META_LATEST_AMOUNT ] ?? 5;

		$args = array(
			'posts_per_page' => $amount,
		);

		return post::get_many( $args );
	}

	/**
	 * Returns most read posts via Koko Analytics
	 */
	public function get_most_read_articles(): array {
		$amount = $this->content_attributes[ LatestAndMostReadPosts::BLOCK_META_MOST_READ_AMOUNT ] ?? 5;

		return KokoAnalytics::get_most_viewed_articles( $amount );
	}
}
