<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Partner;

class ii_partner extends base_object {
	public static $type = Partner::POST_TYPE_NAME;

	public function get_subtitle(): string {
		return get_field( 'subtitle', $this->get_id() ) ?: '';
	}
}
