<?php
namespace Clarkson_Core\WordPress_Object;

class base_term extends Clarkson_Term {
	public function get_formatted_description(): string {
		return term_description( $this->get_id() ) ?: '';
	}
}
