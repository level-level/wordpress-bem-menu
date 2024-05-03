<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Page as PostTypesPage;

class page extends base_object {
	public static $type = PostTypesPage::POST_TYPE_NAME;
}
