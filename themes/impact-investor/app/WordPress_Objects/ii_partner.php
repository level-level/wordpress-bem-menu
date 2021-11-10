<?php

namespace Clarkson_Core\WordPress_Object;

class ii_partner extends base_object {
	public static $type = 'ii_partner';

	public function get_subtitle(): string {
		return get_field( 'subtitle', $this->get_id() ) ?: '';
	}
}
