<?php
namespace FDMGLabels\GravityForms\ADvance;

use Exception;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\UserAPI;
use FDMGLabels\Logging\ADvanceLogger;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\StaticPages\ResponseTrait;
use GF_Field;
use GFAPI;
use Monolog\Registry;
use RGFormsModel;

class ADvanceOptinNewsletterOnSubmission {

	use ResponseTrait;

	private ADvanceLogger $advance_logger;

	public function __construct() {
		$this->advance_logger = new ADvanceLogger();
	}

	public function register_hooks(): void {
		add_action( 'gform_after_submission', array( $this, 'update_user_subscription_by_email_address' ), 10, 2 );
	}

	/**
	 * Update user newsletter subscription in ADvance.
	 * @param array<int|string,mixed> $entry The entry object currently being processed.
	 * @param array<string,mixed> $form The form object currently being processed.
	 * @return void
	 */
	public function update_user_subscription_by_email_address( array $entry, array $form ): void {
		$feed_class = ADvanceNewsletterFeed::get_instance();
		$feed       = $feed_class->get_feed_of_form( (int) $form['id'] );
		if ( ! $feed ) {
			$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'No ADvance feed found for this form.', LogLine::LEVEL_INFO );
			return;
		}

		$map                    = $feed_class->get_map( $feed );
		$field_email_value      = $this->get_mapped_field_value_from_entry( $form, $entry, $map, ADvanceNewsletterFeed::META_EMAIL_ADDRESS );
		$choices                = ( new ADvanceNewsletterFeed() )->get_newsletter_choices();
		$field_newsletter_value = array();

		foreach ( $choices as $choice ) {
			$field_newsletter_value[] = (bool) $this->get_mapped_field_value_from_entry( $form, $entry, $map, $choice['name'] ) ? sprintf( '%s', $choice['value'] ) : '';
		}

		$is_optin = (bool) $this->get_mapped_field_value_from_entry( $form, $entry, $map, ADvanceNewsletterFeed::META_OPTIN_ENABLED );

		try {
			$user_api = new UserAPI( ( new ClientFactory() )->get_client() );
			$response = $user_api->update_newsletter_subscription_by_email_address(
				$field_email_value,
				$field_newsletter_value,
				$is_optin,
			);

			if ( $response->getStatusCode() === 200 ) {
				$body = $user_api->parse_response_to_json( $response );
				// Handle success response, but still an error occurred in processing
				if ( $body['response']['status']['code'] === 'WARNING' || $body['response']['status']['messages']['type'] === 'warn' ) {
					Registry::wordpress()->warning(
						'ADvance login API returned error response',
						array(
							'response' => (string) $response->getBody(),
						)
					);
					return;
				}
				$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Successfully updated newsletter subscriptions in ADvance.', LogLine::LEVEL_INFO );
			} else {
				$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], sprintf( 'Failed to update newsletter subscriptions in ADvance. Status code: %d', $response->getStatusCode() ), LogLine::LEVEL_ERROR );
			}
		} catch ( Exception $e ) {
			$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], sprintf( 'Exception occurred while updating newsletter subscriptions in ADvance: %s', $e->getMessage() ), LogLine::LEVEL_ERROR );
		}
	}

	public function is_optin_form(): bool {
		return true;
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
