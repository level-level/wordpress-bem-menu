<?php
namespace Gutenberg\Blocks;

use Gutenberg\Blocks\base_block;
use LevelLevel\Paywall\ObjectPaywall;

class acf_block_ll_paywall_paywall extends base_block {

	/**
	 * @return ObjectPaywall|null
	 */
	public function get_paywall_object() {
		$post = get_post( (int) $this->post_id );
		return $post ? ObjectPaywall::get_from_post( $post ) : null;
	}
}
