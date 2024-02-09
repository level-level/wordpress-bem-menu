<?php

namespace FDMGLabels\GravityForms;

use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use FDMGLabels\GravityForms\FormSettings\Type;
use GF_Field;
use GF_Field_Email;
use GFAPI;
use GFCommon;

class ConfirmationPageParams {

	protected const URL_PARAM = 'gf-data';

	public function register_hooks(): void {
		add_filter( 'gform_confirmation', array( $this, 'add_entry_data_to_confirmation' ), 10, 3 );
	}

	public function add_entry_data_to_confirmation( string|array $confirmation, array $form, array $entry ): string|array {
		if ( is_array( $confirmation ) && isset( $confirmation['redirect'] ) ) {
			return $this->add_entry_data_to_confirmation_url( $confirmation, $form, $entry );
		}

		if ( is_string( $confirmation ) ) {
			return $this->add_tracking_to_ajax_no_redirect_form( $confirmation, $form, $entry );
		}

		return $confirmation;
	}


	/**
	 * Adds form entry data to the url of the confirmation.
	 * It's first JSON-encoded, and then base64-encoded, to not show the user what's in it.
	 *
	 * @param array<string,mixed> $confirmation
	 * @param array<string,mixed> $form
	 * @param array<mixed> $entry
	 * @return array<string,mixed>
	 */
	public function add_entry_data_to_confirmation_url( string|array $confirmation, array $form, array $entry ): array {
		$confirmation['redirect'] = $this->add_query_arg_to_url( $confirmation['redirect'], $form, $entry );
		return $confirmation;
	}

	/**
	 * Adds the cordaid-gf-submission-data url parameter to the given url.
	 *
	 * @param string $url
	 * @param array<string,mixed> $form
	 * @param array<mixed> $entry
	 * @return string
	 */
	protected function add_query_arg_to_url( string $url, array $form, array $entry ): string {
		$data      = $this->get_entry_data_to_add( $form, $entry );
		$json_data = wp_json_encode( $data ) ?: '';
		$url       = add_query_arg( self::URL_PARAM, rawurlencode( base64_encode( $json_data ) ), $url ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		return $url;
	}

	/**
	 * Get data that should be passed via the confirmation url.
	 * Used on confirmation page, for example for tracking purposes.
	 *
	 * @param array<string,mixed> $form
	 * @param array<mixed> $entry
	 * @return array<string,mixed>
	 */
	public function get_entry_data_to_add( array $form, array $entry ): array {
		$post_id          = url_to_postid( $entry['source_url'] );
		$post             = get_post( $post_id );
		$post_object      = Clarkson_Object::get( $post_id );
		$post_title       = $post ? $post_object->get_title() : '';
		$form_type        = ( new Type() )->get_value( $form );
		$categories       = wp_get_post_categories( $post_id, array( 'fields' => 'names' ) );
		$author           = get_the_author_meta( 'display_name', $post->post_author );
		$publication_date = $post_object->get_date_i18n( 'Y-m-d' );
		$publication_time = $post_object->get_date_i18n( 'H:i' );
		$revisions        = wp_get_latest_revision_id_and_total_count( $post_id );

		$data = array(
			'form_id'               => (int) $form['id'],
			'form_type'             => (string) $form_type, // 'default', 'newsletter', 'download
			'form_title'            => (string) $form['title'],
			'click_text'            => (string) $form['buttonText'],
			'post_id'               => (int) $post_id,
			'post_url'              => (string) $entry['source_url'],
			'post_title'            => (string) $post_title,
			'post_category'         => (array) $categories,
			'post_author'           => (string) $author,
			'entry_id'              => (int) $entry['id'],
			'post_revisions'        => (int) $revisions['count'],
			'post_publication_date' => (string) $publication_date,
			'post_publication_time' => (string) $publication_time,
		);
		return $data;
	}

	/**
	 * Get email address from entry.
	 *
	 * @param array<string|int,mixed> $entry
	 * @param array<string,mixed> $form
	 * @return string
	 */
	public function get_email_address_from_entry( array $entry, array $form ): string {
		// Get the first email field
		$email_field = null;
		foreach ( $form['fields'] as $field ) {
			if ( $field instanceof GF_Field_Email ) {
				$email_field = $field;
				break;
			}
		}

		// If no email field, email is empty
		if ( ! $email_field ) {
			return '';
		}

		// Hash the email address value of the entry
		$entry_email_value = $entry[ $email_field->id ] ?? '';
		return hash( 'sha256', $entry_email_value );
	}

	/**
	 * Ajax forms that don't redirect, can't use our default tracking code.
	 * So instead, we track the same data but now using a script that gets
	 * appended after the confirmation message.
	 *
	 * @param string|array<string,mixed> $confirmation
	 * @param array<string,mixed> $form
	 * @param array<string|int,mixed> $entry
	 * @return string|array<string,mixed>
	 */
	public function add_tracking_to_ajax_no_redirect_form( string|array $confirmation, array $form, array $entry ): string|array {
		$submisison_data             = ( new ConfirmationPageParams() )->get_entry_data_to_add( $form, $entry );
		$submisison_data_json_string = wp_json_encode( $submisison_data );
		if ( ! $submisison_data_json_string ) {
			return $confirmation;
		}

		$confirmation .= Templates::get_instance()->render_twig(
			'gtm/gf-form-confirmation.twig',
			array(
				'submission_data' => $submisison_data,
			),
			true
		);
		return $confirmation;
	}
}
