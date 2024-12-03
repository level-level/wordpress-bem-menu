<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Vacancy;

class ii_vacancy extends base_object {
	public static $type = Vacancy::POST_TYPE_NAME;

	public function get_company_name(): string {
		return get_field( Vacancy::META_COMPANY, $this->get_id() ) ?: '';
	}

	public function get_location(): string {
		return get_field( Vacancy::META_LOCATION, $this->get_id() ) ?: '';
	}
}
