<?php
namespace Gutenberg\Blocks;

use Gutenberg\Blocks\base_block;
use LevelLevel\Paywall\ObjectPaywall;

class acf_block_ll_paywall_paywall extends base_block {

	/**
	 * Get paywall object based on the ACF field values of the block.
	 */
	public function get_paywall_object(): ObjectPaywall {
		return ObjectPaywall::get_from_acf_field_values( is_array( $this->content_attributes ) ? $this->content_attributes : array() );
	}
}
