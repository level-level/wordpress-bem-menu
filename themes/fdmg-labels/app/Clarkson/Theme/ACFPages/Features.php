<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Features {

	protected const FIELD_GROUP = 'features';

	public const MENU_SLUG     = 'acf-features-options';
	public const META_FEATURES = 'features';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Features', 'Options', 'fdmg-labels' ),
				'page_title'  => _x( 'Features', 'Options', 'fdmg-labels' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => ThemeOptions::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Features', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Group::make( _x( 'Features', 'Fields', 'fdmg-labels' ), self::META_FEATURES )
					->instructions( _x( 'Enable or disable features.', 'Fields', 'fdmg-labels' ) )
						->fields(
							array(
								TrueFalse::make( _x( 'Example feature', 'Fields', 'fdmg-labels' ), FeatureManager::FEATURE_EXAMPLE )
									->instructions( _x( 'This is an example functionality.', 'Fields', 'fdmg-labels' ) )
									->stylisedUi(),
							)
						),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
