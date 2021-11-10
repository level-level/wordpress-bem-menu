<?php

namespace Gutenberg\Blocks;

use Clarkson_Core\WordPress_Object\post;

class acf_widget_latest_articles extends base_block {

	/**
	* Get latest posts
	*/
	public function get_latest_articles(): array {
		$amount = $this->content_attributes['amount'] ?? 5;

		$args = array(
			'posts_per_page' => $amount,
		);

		return post::get_many( $args );
	}
}
