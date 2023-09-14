<?php

namespace FDMGLabels\CLI;

use FDMGLabels\GravityForms\Selligent\Feed;
use GFAPI;
use GFFormsModel;
use WP_CLI;
use WP_Error;

class MigrateGFSelligentForms {

	/**
	 * Convert forms to add Selligent feed instead of Selligent settings
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels migrate-gf-selligent-forms run
	 */
	public function run( array $args, array $assoc_args ): void {
		$form_ids = GFFormsModel::get_form_ids( null, null, 'id', 'ASC' ) ?: array();
		if ( empty( $form_ids ) ) {
			WP_CLI::warning( 'No forms found' );
		}

		foreach ( $form_ids as $form_id ) {
			$this->process_item( (int) $form_id );
		}

		WP_CLI::success( 'All items have been processed' );
	}

	/**
	 * Process a single form. If it's a Selligent form, create or update the Selligent feed.
	 */
	protected function process_item( int $form_id ): void {
		// If form cannot be found, quit
		$form = GFAPI::get_form( $form_id );
		if ( ! $form ) {
			WP_CLI::warning( sprintf( 'Form with ID %d not found', $form_id ) );
			return;
		}

		// Only process Selligent forms
		$is_selligent_form = (bool) ( $form['ii_is_selligent_form'] ?? false );
		if ( ! $is_selligent_form ) {
			WP_CLI::log( sprintf( 'Form with ID %d is not a Selligent form', $form_id ) );
			return;
		}

		// Check if a feed already exists for this form
		$current_feed     = ( new Feed() )->get_feed_of_form( $form_id );
		$feed_meta_to_set = array(
			'selligent_enabled' => true,
		);

		// Detect the email field ID if it exists, and add it too the $feed_meta_to_set variable
		$email_field_id = $this->detect_email_field_id( $form );
		if ( $email_field_id ) {
			$feed_meta_to_set['selligent_field_map_email'] = $email_field_id;
		}

		// Create or update the feed
		if ( ! $current_feed ) {
			$feed_modified = GFAPI::add_feed( $form_id, $feed_meta_to_set, Feed::FEED_SLUG );
		} else {
			$feed_modified = GFAPI::update_feed( $current_feed['id'], $feed_meta_to_set, $form_id ); // @phpstan-ignore-line
		}

		// Show log message based on success or failure
		if ( $feed_modified instanceof WP_Error ) {
			WP_CLI::warning( sprintf( 'Failed to %s feed for form with ID %d', $current_feed ? 'update' : 'create', $form_id ) );
			return;
		} else {
			WP_CLI::log( sprintf( 'Form feed %s for form with ID %d', $current_feed ? 'updated' : 'created', $form_id ) );
		}
	}

	/**
	 * Find the first email field in a form, and return it's ID.
	 *
	 * @param array<string,mixed> $form
	 * @return string
	 */
	protected function detect_email_field_id( array $form ): string {
		$email_field_id = '';

		foreach ( $form['fields'] as $field ) {
			if ( $field->type === 'email' ) {
				$email_field_id = (string) $field->id;
				break;
			}
		}

		return $email_field_id;
	}
}
