<?php

namespace FDMGLabels\GravityForms;

use GFAddOn;
use GFForms;
use FDMGLabels\GravityForms\ADvanceNewsletterFeed;

/**
 * This class registers all feeds.
 * Should be registered via post-autoload.php
 */
class Feeds {
	public function register_hooks(): void {
		add_action( 'gform_loaded', array( $this, 'register_feeds' ) );
	}

	public function register_feeds(): void {
		GFForms::include_feed_addon_framework();
		GFAddOn::register( ADvanceNewsletterFeed::class );
	}
}
