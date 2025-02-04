<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Password;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use FDMGLabels\Connectors\FDMGAPI\Configuration;

class FDMGAPI {

	protected const FIELD_GROUP = 'fdmg_api';

	public const MENU_SLUG = 'acf-fdmg-api-options';

	public const OPTION_GROUP           = 'fdmg_api';
	public const OPTION_ENVIRONMENT     = 'environment';
	public const OPTION_CLIENT_ID       = 'client_id';
	public const OPTION_CLIENT_SECRET   = 'client_secret';
	public const OPTION_SITE_CONNECTION = 'site_connection';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'FDMG API', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'FDMG API', 'Options', 'fdmg-labels-admin' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => SiteSettings::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
				'capability'  => 'manage_options',
			)
		);
	}

	public function register_fields(): void {
		$environment_keys = array_keys( Configuration::ENVIRONMENTS );
		$environments     = array_combine(
			$environment_keys, array_map(
				function ( $key ) {
					return sprintf( _x( '%1$s (%2$s)', 'Fields', 'fdmg-labels-admin' ), $key, Configuration::ENVIRONMENTS[ $key ]['base_url'] );
				},
				$environment_keys
			)
		);

		$site_connections = array_combine( Configuration::SITE_CONNECTIONS, Configuration::SITE_CONNECTIONS );

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'FDMG API options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'FDMG API options', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP )
						->fields(
							array(
								Select::make( _x( 'Environment', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ENVIRONMENT )
									->choices( $environments )
									->nullable(),
								Text::make( _x( 'Client ID', 'Fields', 'fdmg-labels-admin' ), self::OPTION_CLIENT_ID ),
								Password::make( _x( 'Client Secret', 'Fields', 'fdmg-labels-admin' ), self::OPTION_CLIENT_SECRET ),
								Select::make( _x( 'Site connection', 'Fields', 'fdmg-labels-admin' ), self::OPTION_SITE_CONNECTION )
									->choices( $site_connections )
									->nullable(),
							)
						),
				),
				'layout'   => 'block',
				'location' => array(
					Location::where( 'options_page', '==', self::MENU_SLUG ),
				),
			)
		);
	}
}
