<?php

namespace FDMGLabels\GravityForms\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Connectors\ADvance\ClientFactory;
use FDMGLabels\Connectors\ADvance\NewsletterAPI;
use GFAPI;
use GFFeedAddOn;

class ADvanceNewsletterFeed extends GFFeedAddOn {
	public const FEED_SLUG = 'fdmg_advance_newsletter';

	// Changable fields
	public const META_ENABLED = 'fdmg_advance_enabled';

	// Field map
	public const META_EMAIL_ADDRESS              = 'fdmg_advance_email_address';
	public const META_FIELD_MAP                  = 'fdmg_advance_field_map';

	// Automatically set meta
	public const META_IS_ADVANCE_ENTRY = 'is_fdmg_advance_entry';

	// phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
	protected $_multiple_feeds           = false;
	protected $_version                  = '1.0';
	protected $_min_gravityforms_version = '2.6';
	protected $_slug                     = self::FEED_SLUG;
	protected $_full_path                = __FILE__;

	/**
	 * @var array<string>
	 */
	protected $_capabilities = array(
		'gravityforms_advance',
		'gravityforms_advance_uninstall',
	);

	/**
	 * @var string
	 */
	protected $_capabilities_settings_page = 'gravityforms_fdmg_advance';

	/**
	 * @var string
	 */
	protected $_capabilities_form_settings = 'gravityforms_fdmg_advance';

	/**
	 * @var string
	 */
	protected $_capabilities_uninstall = 'gravityforms_fdmg_advance_uninstall';

	/**
	 * @var ADvanceNewsletterFeed $_instance
	 */
	private static $_instance = null;
	// phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

	public function __construct() {
		$this->_title       = 'FDMG ADvance'; // Cannot use translation because of load order
		$this->_short_title = 'FDMG ADvance'; // Cannot use translation because of load order
		parent::__construct();
	}

	public static function get_instance(): self {
		if ( self::$_instance === null ) {
			self::$_instance = new ADvanceNewsletterFeed();
		}

		return self::$_instance;
	}

	/**
	 * Configures the settings which should be rendered on the feed edit page
	 *
	 * @return array<mixed>
	 */
	public function feed_settings_fields() {
		$fields = array(
			array(
				'name'          => self::META_ENABLED,
				'type'          => 'toggle',
				'label'         => __( 'Send entries to ADvance as an opt-in', 'fdmg-admin' ),
				'default_value' => 0,
			),
			array(
				'name'       => self::META_FIELD_MAP,
				'type'       => 'field_map',
				'label'      => __( 'Connect fields', 'fdmg-admin' ),
				'tooltip'    => __( 'Connect fields that should be sent to ADvance. These can be bundled into one Gravity Forms field. By just selected the same field for multiple newsletters, you can easily manage subscriptions.', 'fdmg-admin' ),
				'field_map'  => array_merge(
					array(
						array(
							'name'       => self::META_EMAIL_ADDRESS,
							'label'      => __( 'Email Address', 'fdmg-admin' ),
							'value'      => 'email_address',
							'field_type' => array( 'email', 'text' ),
							'required'   => true,
						),
					),
					$this->get_newsletter_choices()
				),
				'dependency' => array(
					'live'   => true,
					'fields' => array(
						array(
							'field' => self::META_ENABLED,
						),
					),
				),
			),
		);

		return array(
			array(
				'title'  => __( 'ADvance settings', 'fdmg-admin' ),
				'fields' => $fields,
			),
		);
	}

	/**
	 * Save extra data in the entry.
	 * When adding meta data, we need to make sure to add it to the entry array, and also call gform_update_meta.
	 * @see https://docs.gravityforms.com/using-entry-meta-with-add-on-framework/#saving-a-value-to-the-entry-meta-key
	 *
	 * @param array<string|mixed> $feed The feed object to be processed.
	 * @param array<mixed> $entry The entry object currently being processed.
	 * @param array<string,mixed> $form The form object currently being processed.
	 *
	 * @return null|array<string|mixed> Modified entry what's going to be updated by GravityForms
	 */
	public function process_feed( $feed, $entry, $form ) {
		if ( ! self::get_meta_of_feed( $feed, self::META_ENABLED, false ) ) {
			return null;
		}

		// Stores if entry needs to be sent to ADvance.
		gform_update_meta( $entry['id'], self::META_IS_ADVANCE_ENTRY, true, $form['id'] );
		$entry[ self::META_IS_ADVANCE_ENTRY ] = true;

		return $entry;
	}

	/**
	 * Gets a meta field of the feed.
	 *
	 * @param array<string,mixed> $feed
	 * @param string $meta_key
	 * @param mixed $default_null
	 * @return mixed
	 */
	public function get_meta_of_feed( array $feed, string $meta_key, mixed $default_null = null ): mixed {
		if ( empty( $feed['meta'] ) ) {
			return $default_null;
		}
		return $feed['meta'][ $meta_key ] ?? $default_null;
	}

	/**
	 * Get the current feed data of the provided form
	 * @return array<string,mixed>|null
	 */
	public function get_feed_of_form( int $form_id ): ?array {
		$feeds = GFAPI::get_feeds( null, $form_id, self::FEED_SLUG, true );
		if ( is_array( $feeds ) && isset( $feeds[0] ) ) {
			return $feeds[0];
		}
		return null;
	}

	/**
	 * Gets the input's advance field. It attempts to extract it from the ADvance feed settings.
	 *
	 * @param array<string,mixed> $feed
	 * @param string $input_id
	 * @return string|null $advance_field For example 13, 14.2, 15.1
	 */
	public function get_advance_field_by_input_id( array $feed, string $input_id ): ?string {
		$map = $this->get_map( $feed );
		foreach ( $map as $map_field_name => $map_input_id ) {
			if ( ( (string) $map_input_id ) === ( (string) $input_id ) ) {
				return $map_field_name;
			}
		}

		return null;
	}

	/**
	 * Get the complete field mapping as a advance_field => input_id array.
	 * @param array<string,mixed> $feed
	 * @return array<string,string>
	 */
	public function get_map( array $feed ): array {
		$map = array();
		if ( empty( $feed['meta'] ) ) {
			return $map; // No feed meta equals no field map fields. Quit early to prevent GF from erroring.
		}

		$field_map = self::get_instance()->get_field_map_fields( $feed, self::META_FIELD_MAP );
		return $field_map;
	}

	/**
	 * Get newsletter choices from ADvance API.
	 *
	 * @return array<array{name: string, label: string, value: string, field_type: array<string>, required: bool}>
	 */
	public function get_newsletter_choices(): array {
		try {
			$newsletter_api = new NewsletterAPI( ( new ClientFactory() )->get_client() );
			$response       = $newsletter_api->get_newsletters_for_brand( true );

			$newsletters = array();
			$body        = $newsletter_api->parse_response_to_json( $response );

			if ( isset( $body['response']['data']['list'] ) && is_array( $body['response']['data']['list'] ) ) {
				foreach ( $body['response']['data']['list'] as $newsletter ) {
					$name = $newsletter['Name'] ?? '';
					$code = $newsletter['Code'] ?? '';

					if ( ! empty( $name ) && ! empty( $code ) ) {
						$newsletters[] = array(
							'name'       => $code,
							'label'      => $name,
							'value'      => $code,
							'field_type' => array( 'checkbox', 'radio', 'select', 'consent' ),
							'required'   => false,
						);
					}
				}
			}

			return $newsletters;
		} catch ( \Exception $e ) {
			// Log error and return empty array if API call fails
			error_log( 'ADvance Newsletter API Error: ' . $e->getMessage() );
			return array();
		}
	}
}
