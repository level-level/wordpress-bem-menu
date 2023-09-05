<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\LatestComments;
use Clarkson_Core\WordPress_Object\comment;

class acf_widget_latest_comments extends base_block {

	/**
	* Get latest comments
	* @return \Clarkson_Core\WordPress_Object\Clarkson_Comment[]
	*/
	public function get_latest_comments(): array {
		$amount = $this->content_attributes[ LatestComments::WIDGET_META_AMOUNT ] ?? 5;
		$args   = array(
			'number' => $amount,
			'status' => 1, // approved comments only
			'order'  => 'DESC',
		);

		return comment::get_many( $args );
	}
}
