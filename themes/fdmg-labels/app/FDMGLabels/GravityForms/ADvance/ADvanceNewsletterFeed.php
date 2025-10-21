<?php

namespace FDMGLabels\GravityForms\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use GFAPI;
use GFFeedAddOn;

class ADvanceNewsletterFeed extends GFFeedAddOn {
	public const FEED_SLUG = 'fdmg_advance_newsletter';

	// Changable fields
	public const META_ENABLED = 'fdmg_advance_enabled';

	// Field map
	public const META_FIELD_MAP                  = 'fdmg_advance_field_map';
	public const META_FIELD_MAP_OPTIN_NEWSLETTER = 'optin_newsletter';

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
		$this->_title       = 'FDMG Advance'; // Cannot use translation because of load order
		$this->_short_title = 'FDMG Advance'; // Cannot use translation because of load order
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
		foreach( get_field( sprintf( '%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_SUBSCRIPTION_PRODUCTS ), 'option' ) as $newsletter_ids ) {
			$populate_newsletter_choices[] = array(
				'name'       => self::META_FIELD_MAP_OPTIN_NEWSLETTER . (string) $newsletter_ids['product_id'],
				'label'      => __( sprintf( 'Newsletter optin %s', (string) $newsletter_ids['product_id'] ), 'fdmg--admin' ),
				'field_type' => array( 'consent' ),
				'required'   => false,
			);
		}

		$fields = array(
			array(
				'name'          => self::META_ENABLED,
				'type'          => 'toggle',
				'label'         => __( 'Send entries to ADvance as an opt-in', 'fdmg--admin' ),
				'default_value' => 0,
			),
			array(
				'name'       => self::META_FIELD_MAP,
				'type'       => 'field_map',
				'label'      => __( 'Connect fields', 'fdmg--admin' ),
				'tooltip'    => __( 'Connect fields that should be sent to ADvance.', 'fdmg--admin' ),
				'field_map'  => $populate_newsletter_choices,
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

}
