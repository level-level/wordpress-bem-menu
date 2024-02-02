<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Location;

class SEOOptions {

	protected const FIELD_GROUP = 'seo_options';

	public const MENU_SLUG = 'acf-seo-options-options';

	public const OPTION_GROUP_ROBOTS            = 'robots_options';
	public const OPTION_ROBOTS_CUSTOM_CONTENT   = 'custom_content';
	public const OPTION_ROBOTS_COPYRIGHT_NOTICE = 'copyright_notice';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'SEO options', 'Options', 'fdmg-labels' ),
				'page_title'  => _x( 'SEO options', 'Options', 'fdmg-labels' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => ThemeOptions::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
				'capability'  => 'manage_options',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'SEO options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Robots', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP_ROBOTS )
						->fields(
							array(
								Textarea::make( _x( 'Copyright notice', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ROBOTS_COPYRIGHT_NOTICE )
									->instructions( _x( 'You may add a copyright notice. This piece of text will be converted to a comment, and prepended to the robots.txt file.', 'Fields', 'fdmg-labels-admin' ) ),
								Textarea::make( _x( 'Custom content', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ROBOTS_CUSTOM_CONTENT )
									->instructions( _x( 'Add custom content to the robots.txt file.', 'Fields', 'fdmg-labels-admin' ) ),
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
