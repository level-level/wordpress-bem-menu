<?php
namespace FDMGLabels\GravityForms\ADvance;

use Exception;
use FDMGLabels\Logging\ADvanceLogger;
use FDMGLabels\Logging\LogLine;
use GFAPI;
use WP_Error;

class ADvanceOptinNewsletterOnSubmission {

	public const TRIGGER = 'fdmg_advance_optin_newsletter';

	private ADvanceLogger $advance_logger;

	public function __construct() {
		$this->advance_logger = new ADvanceLogger();
	}

	public function register_hooks(): void {
		add_action( 'gform_after_submission', array( $this, 'schedule_sync' ), 10, 2 );
		add_action( self::TRIGGER, array( $this, 'sync' ) );
	}

	/**
	 * Schedule optin in ADvance.
	 * @param array<int|string,mixed> $entry The entry object currently being processed.
	 * @param array<string,mixed> $form The form object currently being processed.
	 * @return void
	 */
	public function schedule_sync( array $entry, array $form ): void {
		$feed_class = ADvanceNewsletterFeed::get_instance();
		$feed       = $feed_class->get_feed_of_form( (int) $form['id'] );
		if ( ! $feed ) {
			return;
		}

		$is_enabled = (bool) $feed_class->get_meta_of_feed( $feed, ADvanceNewsletterFeed::META_ENABLED, false );
		if ( ! $is_enabled ) {
			return;
		}

		// Schedule single event
		$scheduled = as_schedule_single_action( time(), self::TRIGGER, array( (int) $entry['id'] ), 'fdmg_advance' );
		if ( ! $scheduled ) {
			$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Subscribing to the newsletter has failed to be queued.', LogLine::LEVEL_ERROR );
			return;
		}

		$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Subscribing to the newsletter has been queued.', LogLine::LEVEL_INFO );
	}

	/**
	 * Register optin in ADvance.
	 */
	public function sync( int $entry_id ): void {
		// Get the entry object
		$entry = GFAPI::get_entry( $entry_id );
		if ( $entry instanceof WP_Error ) {
			$this->advance_logger->add_gravityforms_entry_log(
				$entry_id, sprintf(
					'Could not subscribe to the newsletter because the entry does not exist: %1$s - %2$s.',
					(string) $entry->get_error_code(),
					$entry->get_error_message()
				), LogLine::LEVEL_ERROR
			);
			return;
		}

		// Get the form corresponding to the entry
		$form = GFAPI::get_form( $entry['form_id'] );
		if ( ! is_array( $form ) ) {
			$this->advance_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the form does not exist.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the feed
		$feed_class = ADvanceNewsletterFeed::get_instance();
		$feed       = $feed_class->get_feed_of_form( $form['id'] );
		if ( ! $feed ) {
			$this->advance_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the ADvance feed does not exist.', LogLine::LEVEL_ERROR );
			return;
		}

		$this->advance_logger->add_gravityforms_entry_log( $entry_id, 'Newsletter subscription processing completed.', LogLine::LEVEL_SUCCESS );
	}
}
