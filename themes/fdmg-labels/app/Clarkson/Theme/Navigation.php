<?php
namespace Clarkson\Theme;

class Navigation {

	public function register_hooks(): void {
		add_action( 'after_setup_theme', array( $this, 'register_custom_nav_menus' ) );
	}

	/**
	 * Register wp_nav_menu() menus
	 * source: http://codex.wordpress.org/Function_Reference/register_nav_menus
	 */
	public function register_custom_nav_menus(): void {
		register_nav_menus(
			array(
				'primary_navigation'  => _x( 'Primary navigation', 'Navigation', 'fdmg-labels' ),
				'about_navigation'    => _x( 'About navigation', 'Navigation', 'fdmg-labels' ),
				'general_navigation'  => _x( 'General navigation', 'Navigation', 'fdmg-labels' ),
				'services_navigation' => _x( 'Services navigation', 'Navigation', 'fdmg-labels' ),
				'fdmg_navigation'     => _x( 'FDMG navigation', 'Navigation', 'fdmg-labels' ),
			)
		);
	}
}
