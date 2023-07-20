<?php
namespace FDMGLabels\GravityForms\Selligent;

use FDMGLabels\Connectors\Selligent\Client;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use GF_Field;
use GFAPI;
use RGFormsModel;
use WP_Error;

class OptinNewsletterOnSubmission {

	public const TRIGGER = 'fdmg_labels_optin_newsletter';

	protected SelligentLogger $selligent_logger;

	public function __construct() {
		$this->selligent_logger = new SelligentLogger();
	}

	public function register_hooks(): void {
		add_action( 'gform_after_submission', array( $this, 'schedule_sync' ), 10, 2 );
		add_action( self::TRIGGER, array( $this, 'sync' ) );
	}

	/**
	 * Schedule optin in selligent.
	 * @param array<int|string,mixed> $entry The entry object currently being processed.
	 * @param array<string,mixed> $form The form object currently being processed.
	 * @return void
	 */
	public function schedule_sync( array $entry, array $form ): void {
		$feed_class = Feed::get_instance();
		$feed       = $feed_class->get_feed_of_form( (int) $form['id'] );
		if ( ! $feed ) {
			return;
		}

		$is_enabled = (bool) $feed_class->get_meta_of_feed( $feed, Feed::META_ENABLED, false );
		if ( ! $is_enabled ) {
			return;
		}

		// Schedule single event
		wp_schedule_single_event( time(), self::TRIGGER, array( (int) $entry['id'] ) );

		$this->selligent_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Subscribing to the newsletter has been queued.', LogLine::LEVEL_INFO );
	}

	/**
	 * Register optin in selligent.
	 */
	public function sync( int $entry_id ): void {
		// Get the entry object
		$entry = GFAPI::get_entry( $entry_id );
		if ( $entry instanceof WP_Error ) {
			$this->selligent_logger->add_gravityforms_entry_log(
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
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the form does not exist.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the feed
		$feed_class = Feed::get_instance();
		$feed       = $feed_class->get_feed_of_form( $form['id'] );
		if ( ! $feed ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the Selligent feed does not exist.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the email field input id
		$map            = $feed_class->get_map( $feed );
		$email_input_id = $map[ Feed::META_FIELD_MAP_EMAIL ] ?? null;
		if ( ! $email_input_id ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the email field is not mapped.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the email field
		$email_field = GFAPI::get_field( $form, $email_input_id );
		if ( ! $email_field instanceof GF_Field ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the form does not contain a field mapped to email.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the value from the email field
		$field_email_value = RGFormsModel::get_lead_field_value( $entry, $email_field );
		if ( ! is_string( $field_email_value ) || ! filter_var( $field_email_value, FILTER_VALIDATE_EMAIL ) ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the entry has no valid e-mail address.', LogLine::LEVEL_ERROR );
			return;
		}

		// Subscribe to the newsletter
		$client   = new Client();
		$response = $client->optin_email( $field_email_value );
		if ( $response instanceof WP_Error ) {
			$this->selligent_logger->add_gravityforms_entry_log(
				$entry_id, sprintf(
					'Could not subscribe to the newsletter: %1$s - %2$s.',
					(string) $response->get_error_code(),
					$response->get_error_message()
				), LogLine::LEVEL_ERROR
			);
			return;
		}
		$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Subscribing to the newsletter has finished.', LogLine::LEVEL_SUCCESS );
	}
}
