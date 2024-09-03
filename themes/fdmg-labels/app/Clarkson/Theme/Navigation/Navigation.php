<?php
namespace Clarkson\Theme\Navigation;

use Clarkson\Theme\SVGIcons;
use Extended\ACF\Fields\Select;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Navigation {

	public const PRIMARY_NAV  = 'primary_navigation';
	public const ABOUT_NAV    = 'about_navigation';
	public const GENERAL_NAV  = 'general_navigation';
	public const SERVICES_NAV = 'services_navigation';
	public const FDMG_NAV     = 'fdmg_navigation';
	public const PROFILE_NAV  = 'profile_navigation';

	public const FIELD_GROUP          = 'navigation';
	public const NAVIGATION_META_ICON = 'navigation_meta_icon';

	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_action( 'after_setup_theme', array( $this, 'register_custom_nav_menus' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['navigation'] = $this;
		return $context;
	}

	/**
	 * Register wp_nav_menu() menus
	 * source: http://codex.wordpress.org/Function_Reference/register_nav_menus
	 */
	public function register_custom_nav_menus(): void {
		register_nav_menus(
			array(
				self::PRIMARY_NAV  => _x( 'Primary navigation', 'Navigation', 'fdmg-labels-admin' ),
				self::ABOUT_NAV    => _x( 'About navigation', 'Navigation', 'fdmg-labels-admin' ),
				self::GENERAL_NAV  => _x( 'General navigation', 'Navigation', 'fdmg-labels-admin' ),
				self::SERVICES_NAV => _x( 'Services navigation', 'Navigation', 'fdmg-labels-admin' ),
				self::FDMG_NAV     => _x( 'FDMG navigation', 'Navigation', 'fdmg-labels-admin' ),
				self::PROFILE_NAV  => _x( 'Profile navigation', 'Navigation', 'fdmg-labels-admin' ),
			)
		);
	}

	public function get_menu( string $theme_location, string $menu_class, int $depth = 0, bool $show_icons = false ): string {

		if ( ! has_nav_menu( $theme_location ) ) {
			return '';
		}

		$args = array(
			'theme_location' => $theme_location,
			'menu_class'     => $menu_class,
			'items_wrap'     => '<ul class="%2$s">%3$s</ul>',
			'echo'           => false,
			'walker'         => new WalkerMenu( $menu_class, $show_icons ),
			'depth'          => $depth,
		);

		$nav_menu = wp_nav_menu( $args );

		return is_string( $nav_menu ) ? $nav_menu : '';
	}

	/**
	 * Adds option to add a menu item icon to the primary nav when using the compact header option
	 */
	public function register_fields(): void {
		$icon_menu_locations = array(
			Location::where( 'nav_menu_item', 'location/' . self::PROFILE_NAV ),
		);

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_COMPACT_HEADER ) ) {
			$icon_menu_locations[] = Location::where( 'nav_menu_item', 'location/' . self::PRIMARY_NAV );
		}

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Icon', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Select::make( _x( 'Select a menu item icon', 'Fields', 'fdmg-labels-admin' ), self::NAVIGATION_META_ICON )
						->choices(
							SVGIcons::get_instance()->get_icon_list(),
						)
						->allowNull()
						->stylisedUi(),
				),
				'location' => $icon_menu_locations,
			)
		);
	}
}
