<?php

namespace Gutenberg\Blocks;

use Clarkson_Core\WordPress_Object\post;
use ImpactInvestor\KokoAnalytics\KokoAnalytics;

class acf_block_latest_and_most_read_posts extends base_block {

	/**
	* Get latest posts
	*/
	public function get_latest_articles( $amount = 5 ): array {
		$args = array(
			'posts_per_page' => $amount,
		);

		return post::get_many( $args );
	}

	/**
	 * Returns most read posts via Koko Analytics
	 */
	public function get_most_read_articles( $amount = 5 ): array {
		return KokoAnalytics::get_most_viewed_articles( $amount );
	}
}
