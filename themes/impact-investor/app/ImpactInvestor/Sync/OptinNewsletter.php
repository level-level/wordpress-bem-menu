<?php
namespace ImpactInvestor\Sync;

use GF_Field;
use GFAPI;
use ImpactInvestor\Connectors\Selligent\Client;
use ImpactInvestor\GravityForms\FormSettings\IsSelligentForm;
use ImpactInvestor\Logging\LogLine;
use ImpactInvestor\Logging\Selligent\SelligentLogger;
use RGFormsModel;
use WP_Error;

class OptinNewsletter extends BaseSync {

	public const TRIGGER = 'ii_optin_newsletter';

	/** @var SelligentLogger */
	protected $selligent_logger;

	public function __construct() {
		$this->selligent_logger = new SelligentLogger();
	}

	public function register_hooks(): void {
		parent::register_hooks();
		add_action( self::TRIGGER, array( $this, 'sync' ) );
	}

	public function add_schedule_action(): void {
		add_action( 'gform_after_submission', array( $this, 'schedule_sync' ), 10, 2 );
	}

	public function remove_schedule_action(): void {
		remove_action( 'gform_after_submission', array( $this, 'schedule_sync' ), 10 );
	}

	public function schedule_sync( array $entry, array $form ): void {
		if ( ! ( new IsSelligentForm() )->get_value( $form ) ) {
			return;
		}

		// Schedule single event
		wp_schedule_single_event( time(), self::TRIGGER, array( (int) $entry['id'] ) );

		$this->selligent_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Subscribing to the newsletter has been queued.', LogLine::LEVEL_INFO );
	}

	/**
	 * Register articles in selligent, and then send the newsletter.
	 */
	public function sync( int $entry_id ): void {
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

		$form = GFAPI::get_form( $entry['form_id'] );
		if ( ! is_array( $form ) ) {
			$this->selligent_logger->add_gravityforms_entry_log( $entry_id, 'Could not subscribe to the newsletter because the form does not exist.', LogLine::LEVEL_ERROR );
			return;
		}

		$email_field = null;
		foreach ( $form['fields'] as $field ) {
			if ( $field->type === 'email' ) {
				$email_field = $field;
				break;
			}
		}
		if ( ! $email_field instanceof GF_Field ) {
			$this->selligent_logger->add_gravityforms_entry_log(
				$entry_id, sprintf(
					'Could not subscribe to the newsletter because the form does not contain a field of the type "%s".',
					__( 'Email', 'gravityforms' )
				), LogLine::LEVEL_ERROR
			);
			return;
		}

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
