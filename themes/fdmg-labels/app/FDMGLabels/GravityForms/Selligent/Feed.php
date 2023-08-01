<?php

namespace FDMGLabels\GravityForms\Selligent;

use GFAPI;
use GFFeedAddOn;

class Feed extends GFFeedAddOn {

	public const FEED_SLUG = 'selligent';

	// Changable fields
	public const META_ENABLED = 'selligent_enabled';

	// Field map
	public const META_FIELD_MAP       = 'selligent_field_map';
	public const META_FIELD_MAP_EMAIL = 'email';

	// Automatically set meta
	public const META_IS_SELLIGENT_ENTRY = 'is_selligent_entry';

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
		'gravityforms_selligent',
		'gravityforms_selligent_uninstall',
	);

	/**
	 * @var string
	 */
	protected $_capabilities_settings_page = 'gravityforms_selligent';

	/**
	 * @var string
	 */
	protected $_capabilities_form_settings = 'gravityforms_selligent';

	/**
	 * @var string
	 */
	protected $_capabilities_uninstall = 'gravityforms_selligent_uninstall';

	/**
	 * @var Feed $_instance
	 */
	private static $_instance = null;
	// phpcs:enable PSR2.Classes.PropertyDeclaration.Underscore

	public function __construct() {
		$this->_title       = __( 'Selligent', 'fdmg-labels-admin' );
		$this->_short_title = __( 'Selligent', 'fdmg-labels-admin' );
		parent::__construct();
	}

	public static function get_instance(): self {
		if ( self::$_instance === null ) {
			self::$_instance = new Feed();
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
				'label'         => __( 'Send entries to Selligent', 'fdmg-labels-admin' ),
				'default_value' => 0,
			),
			array(
				'name'       => self::META_FIELD_MAP,
				'type'       => 'field_map',
				'label'      => __( 'Connect fields', 'fdmg-labels-admin' ),
				'tooltip'    => __( 'Connect fields that should be sent to Selligent.', 'fdmg-labels-admin' ),
				'field_map'  => array(
					array(
						'name'       => self::META_FIELD_MAP_EMAIL,
						'label'      => __( 'E-mail address', 'fdmg-labels-admin' ),
						'field_type' => array( 'email' ),
						'required'   => true,
					),
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
				'title'  => __( 'Selligent settings', 'fdmg-labels-admin' ),
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

		// Stores if entry needs to be sent to Selligent.
		gform_update_meta( $entry['id'], self::META_IS_SELLIGENT_ENTRY, true, $form['id'] );
		$entry[ self::META_IS_SELLIGENT_ENTRY ] = true;
		return $entry;
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
	 * Gets a meta field of the feed.
	 *
	 * @param array<string,mixed> $feed
	 * @param string $meta_key
	 * @param mixed $default
	 * @return mixed
	 */
	public function get_meta_of_feed( array $feed, string $meta_key, mixed $default = null ): mixed {
		if ( empty( $feed['meta'] ) ) {
			return $default;
		}
		return $feed['meta'][ $meta_key ] ?? $default;
	}

	/**
	 * Gets the input's selligent field. It attempts to extract it from the Selligent feed settings.
	 *
	 * @param array<string,mixed> $feed
	 * @param string $input_id
	 * @return string|null $selligent_field For example 13, 14.2, 15.1
	 */
	public function get_selligent_field_by_input_id( array $feed, string $input_id ): ?string {
		$map = $this->get_map( $feed );
		foreach ( $map as $map_field_name => $map_input_id ) {
			if ( ( (string) $map_input_id ) === ( (string) $input_id ) ) {
				return $map_field_name;
			}
		}

		return null;
	}

	/**
	 * Get the complete field mapping as a selligent_field => input_id array.
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
}
