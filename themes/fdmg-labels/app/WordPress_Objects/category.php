<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\Taxonomies\Category as TaxonomiesCategory;

class category extends base_term {
	public static $taxonomy = TaxonomiesCategory::TAXONOMY_NAME;
}
