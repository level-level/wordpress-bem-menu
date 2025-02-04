<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Password;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use FDMGLabels\Connectors\FDMGContentAPI\Configuration;

class FDMGContentAPI {

	protected const FIELD_GROUP = 'fdmg_content_api';

	public const MENU_SLUG = 'acf-fdmg-content-api-options';

	public const OPTION_GROUP           = 'fdmg_content_api';
	public const OPTION_ENABLED         = 'enabled';
	public const OPTION_DOMAIN          = 'domain';
	public const OPTION_API_KEY         = 'api_key';
	public const OPTION_SITE_CONNECTION = 'site_connection';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'FDMG Content API', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'FDMG Content API', 'Options', 'fdmg-labels-admin' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => SiteSettings::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
				'capability'  => 'manage_options',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'FDMG Content API options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'FDMG Content API options', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP )
						->fields(
							array(
								TrueFalse::make( _x( 'Enabled', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ENABLED )
									->helperText( _x( 'Enable sending articles to the FDMG Content API.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),

								Select::make( _x( 'Domain', 'Fields', 'fdmg-labels-admin' ), self::OPTION_DOMAIN )
									->choices(
										array(
											Configuration::DOMAIN_DEV  => sprintf( _x( 'Development (%s)', 'Fields', 'fdmg-labels-admin' ), Configuration::DOMAIN_DEV ),
											Configuration::DOMAIN_ACC  => sprintf( _x( 'Acceptance (%s)', 'Fields', 'fdmg-labels-admin' ), Configuration::DOMAIN_ACC ),
											Configuration::DOMAIN_PROD => sprintf( _x( 'Production (%s)', 'Fields', 'fdmg-labels-admin' ), Configuration::DOMAIN_PROD ),
										)
									)
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
								Password::make( _x( 'API Key', 'Fields', 'fdmg-labels-admin' ), self::OPTION_API_KEY )
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
								Select::make( _x( 'Site connection', 'Fields', 'fdmg-labels-admin' ), self::OPTION_SITE_CONNECTION )
									->choices(
										array_combine( array_keys( Configuration::HOOK_NAMES ), array_keys( Configuration::HOOK_NAMES ) )
									)
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
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
