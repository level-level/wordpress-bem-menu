<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Location;

class Selligent {

	protected const FIELD_GROUP = 'selligent';

	public const MENU_SLUG = 'acf-selligent-options';

	public const OPTION_GROUP                 = 'selligent';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Selligent', 'Options', 'fdmg-labels' ),
				'page_title'  => _x( 'Selligent', 'Options', 'fdmg-labels' ),
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
				'title'    => _x( 'Selligent options', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Group::make( _x( 'Selligent options', 'Fields', 'fdmg-labels' ), self::OPTION_GROUP )
						->fields( array (

						) ),
				),
				'layout'   => 'block',
				'location' => array(
					Location::where( 'options_page', '==', self::MENU_SLUG ),
				),
			)
		);
	}
}
