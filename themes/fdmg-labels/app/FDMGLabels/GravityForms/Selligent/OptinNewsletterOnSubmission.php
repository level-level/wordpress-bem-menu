<?php
namespace FDMGLabels\GravityForms\Selligent;

use Exception;
use FDMGLabels\ActionScheduler\ActionSchedulerSetup;
use FDMGLabels\Commands\OptinEmailForNewsletter;
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
		as_schedule_single_action( time(), self::TRIGGER, array( (int) $entry['id'] ), ActionSchedulerSetup::GROUP );

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

		// Get the value of the email field
		$map               = $feed_class->get_map( $feed );
		$field_email_value = $this->get_mapped_field_value_from_entry( $form, $entry, $map, Feed::META_FIELD_MAP_EMAIL );
		if ( ! is_string( $field_email_value ) || ! filter_var( $field_email_value, FILTER_VALIDATE_EMAIL ) ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the entry has no valid e-mail address.', LogLine::LEVEL_ERROR );
			return;
		}

		// Get the value of the commercial optin field
		$field_commercial_optin_value = (bool) $this->get_mapped_field_value_from_entry( $form, $entry, $map, Feed::META_FIELD_MAP_OPTIN_COMMERCIAL );
		$field_research_optin_value   = (bool) $this->get_mapped_field_value_from_entry( $form, $entry, $map, Feed::META_FIELD_MAP_OPTIN_RESEARCH );

		// Subscribe to the newsletter
		try {
			$command = new OptinEmailForNewsletter( $field_email_value, $field_commercial_optin_value, $field_research_optin_value );
			$command->execute();
		} catch ( Exception $e ) {
			$this->selligent_logger->add_gravityforms_entry_log(
				$entry_id, sprintf(
					'Could not subscribe to the newsletter: %1$s - %2$s.',
					(string) $e->getCode(),
					$e->getMessage()
				), LogLine::LEVEL_ERROR
			);
			return;
		}
		$this->selligent_logger->add_gravityforms_entry_log( $entry_id, sprintf( 'Subscribing %s to the newsletter has finished.', $field_email_value ), LogLine::LEVEL_SUCCESS );
	}

	/**
	 * Gets the value of a field from an entry.
	 * The field can be selected from the mapped fields of the form.
	 *
	 * @param array<string,mixed> $form
	 * @param array<mixed> $entry
	 * @param array<string,mixed> $map
	 * @param string $mapped_field_name
	 * @return mixed
	 */
	protected function get_mapped_field_value_from_entry( array $form, array $entry, array $map, string $mapped_field_name ): mixed {
		// Get the field input id
		$email_input_id = $map[ $mapped_field_name ] ?? null;
		if ( ! $email_input_id ) {
			return null;
		}

		// Get the field object
		$field = GFAPI::get_field( $form, $email_input_id );
		if ( ! $field instanceof GF_Field ) {
			return null;
		}

		$field_value = RGFormsModel::get_lead_field_value( $entry, $field );

		// If input ID is for a sub field, get the value from the sub field
		if ( is_array( $field_value ) && str_contains( (string) $email_input_id, '.' ) ) {
			$field_value = $field_value[ $email_input_id ] ?? null;
		}
		return $field_value;
	}
}
