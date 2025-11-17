<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\ADvance\ADvanceNewsletterFeed;
use GFAddOn;
use GFForms;

/**
 * This class registers all feeds.
 * Should be registered via post-autoload.php
 */
class Feeds {
	public const ADVANCE_OPTIN_NEWSLETTER_HOOK = 'fdmg_advance_optin_newsletter_on_submission';

	public function register_hooks(): void {
		add_action( 'gform_loaded', array( $this, 'register_feeds' ) );
		add_action( self::ADVANCE_OPTIN_NEWSLETTER_HOOK, array( $this, 'handle_optin_newsletter_on_submission' ), 10, 2 );
	}

	public function register_feeds(): void {
		GFForms::include_feed_addon_framework();
		GFAddOn::register( ADvanceNewsletterFeed::class );
	}

	public function handle_optin_newsletter_on_submission( int $entry_id, int $feed_id ): void {
		if ( ! $entry_id && ! $feed_id ) {
			return;
		}
		$command = new \FDMGLabels\GravityForms\ADvance\ADvanceOptinNewsletterOnSubmission( $entry_id, $feed_id );
		$command->execute();
	}
}
