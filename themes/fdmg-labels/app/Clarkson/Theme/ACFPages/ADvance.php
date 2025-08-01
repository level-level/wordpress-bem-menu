<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Password;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\URL;
use Extended\ACF\Location;

class ADvance {

	protected const FIELD_GROUP = 'advance_options';

	public const MENU_SLUG = 'acf-advance-options';

	public const OPTION_GROUP           = 'advance';
	public const OPTION_ENABLED         = 'enabled';
	public const OPTION_PAGES_BASE_URL  = 'pages_base_url';
	public const OPTION_API_BASE_URL          = 'api_base_url';
	public const OPTION_API_ACCESS_TOKEN         = 'api_access_token';
	public const OPTION_API_ACCESS_HASH = 'api_access_hash';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'ADvance', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'ADvance', 'Options', 'fdmg-labels-admin' ),
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
				'title'    => _x( 'ADvance options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'ADvance options', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP )
						->fields(
							array(
								TrueFalse::make( _x( 'Enabled', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ENABLED )
									->helperText( _x( 'Enable authentication via the ADvance Identity Provider.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized(),
								URL::make( _x( 'Pages base URL', 'Fields', 'fdmg-labels-admin' ), self::OPTION_PAGES_BASE_URL )
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
								URL::make( _x( 'API base URL', 'Fields', 'fdmg-labels-admin' ), self::OPTION_API_BASE_URL )
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
								Password::make( _x( 'API access token', 'Fields', 'fdmg-labels-admin' ), self::OPTION_API_ACCESS_TOKEN )
									->required()
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::OPTION_GROUP . '_' . self::OPTION_ENABLED, '==', '1' ),
										)
									),
								Password::make( _x( 'API access hash', 'Fields', 'fdmg-labels-admin' ), self::OPTION_API_ACCESS_HASH )
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
