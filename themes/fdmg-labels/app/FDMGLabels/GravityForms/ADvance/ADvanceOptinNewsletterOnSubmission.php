<?php
namespace FDMGLabels\GravityForms\ADvance;

use Exception;
use FDMGLabels\Commands\CommandInterface;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\UserAPI;
use FDMGLabels\Logging\ADvanceLogger;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\StaticPages\ResponseTrait;
use GF_Field;
use GFAPI;
use Monolog\Registry;
use RGFormsModel;

class ADvanceOptinNewsletterOnSubmission implements CommandInterface {

	use ResponseTrait;

	private ADvanceLogger $advance_logger;

	private int $entry_id;

	private int $feed_id;

	/**
	 * Constructor.
	 *
	 * @param int $entry_id The entry object currently being processed.
	 * @param int $feed_id The feed object currently being processed.
	 */
	public function __construct( int $entry_id, int $feed_id ) {
		$this->advance_logger = new ADvanceLogger();
		$this->entry_id       = $entry_id;
		$this->feed_id        = $feed_id;
	}

	public function execute(): bool {
		$entry = GFAPI::get_entry( $this->entry_id );
		if ( $entry instanceof \WP_Error ) {
			$this->advance_logger->add_gravityforms_entry_log( $this->entry_id, 'Failed to retrieve GravityForms entry.', LogLine::LEVEL_ERROR );
			return false;
		}

		$feed = GFAPI::get_feed( $this->feed_id );
		if ( $feed instanceof \WP_Error ) {
			$this->advance_logger->add_gravityforms_entry_log( $this->feed_id, 'Failed to retrieve GravityForms feed.', LogLine::LEVEL_ERROR );
			return false;
		}

		$form = GFAPI::get_form( (int) $entry['form_id'] );
		if ( $form instanceof \WP_Error ) {
			$this->advance_logger->add_gravityforms_entry_log( $this->entry_id, sprintf( 'Failed to retrieve GravityForms form. On entry id %d', $this->entry_id ), LogLine::LEVEL_ERROR );
			return false;
		}

		$this->update_user_subscription_by_email_address( $entry, $form, $feed );
		return true;
	}

	/**
	 * Update user newsletter subscription in ADvance.
	 * @param array<int|string,mixed> $entry The entry object currently being processed.
	 * @param array<string,mixed> $form The form object currently being processed.
	 * @param array<string,mixed> $feed The feed object currently being processed.
	 * @return void
	 */
	private function update_user_subscription_by_email_address( array $entry, array $form, array $feed ): void {
		if ( ! $feed ) {
			$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'No ADvance feed found for this form.', LogLine::LEVEL_INFO );
			return;
		}

		$advance_newsletter_feed = new ADvanceNewsletterFeed();

		$map                    = $advance_newsletter_feed->get_map( $feed );
		$field_email_value      = $this->get_mapped_field_value_from_entry( $form, $entry, $map, ADvanceNewsletterFeed::META_EMAIL_ADDRESS );
		$field_newsletter_value = array();

		// Get the opt-in setting from the feed (not from form field)
		$is_optin = (bool) $advance_newsletter_feed->get_meta_of_feed( $feed, ADvanceNewsletterFeed::META_OPTIN_ENABLED, true );

		$newsletter_codes = array();
		foreach ( $advance_newsletter_feed->get_newsletter_choices() as $newsletter ) {
			$newsletter_codes[] = $newsletter['name'];
		}
		// Only process newsletters that are actually mapped in this feed
		// phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
		foreach ( $map as $mapped_field_name => $input_id ) {
			// Skip the email field as it's not a newsletter
			if ( ! in_array( $mapped_field_name, $newsletter_codes, true ) ) {
				continue;
			}

			// Get the field value for this mapped newsletter
			$field_value = $this->get_mapped_field_value_from_entry( $form, $entry, $map, $mapped_field_name );

			// If the field has a value (user selected/checked it), add the newsletter code
			if ( ! empty( $field_value ) ) {
				$field_newsletter_value[] = $mapped_field_name;
			}
		}

		try {
			$user_api = new UserAPI( ( new ClientFactory() )->get_client() );
			$response = $user_api->update_newsletter_subscription_by_email_address(
				$field_email_value,
				$field_newsletter_value,
				$is_optin,
				false,
				null,
				sprintf( 'GravityForms on site: %s, with GF entry id: %d', get_current_site()->site_name, (int) $entry['id'] )
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
					$warning_message = $body['response']['status']['messages']['message'] ?? 'Unknown warning';
					$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], sprintf( 'Newsletter subscription completed with warning: %s', $warning_message ), LogLine::LEVEL_WARNING );
					return;
				}
				$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], 'Successfully updated newsletter subscriptions in ADvance.', LogLine::LEVEL_SUCCESS );
			} else {
				$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], sprintf( 'Failed to update newsletter subscriptions in ADvance. Status code: %d', $response->getStatusCode() ), LogLine::LEVEL_ERROR );
			}
		} catch ( Exception $e ) {
			$this->advance_logger->add_gravityforms_entry_log( (int) $entry['id'], sprintf( 'Exception occurred while updating newsletter subscriptions in ADvance: %s', $e->getMessage() ), LogLine::LEVEL_ERROR );
		}
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
	private function get_mapped_field_value_from_entry( array $form, array $entry, array $map, string $mapped_field_name ): mixed {
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
