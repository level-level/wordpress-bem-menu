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
				'primary_navigation'  => __( 'Primary navigation', 'fdmg-labels' ),
				'about_navigation'    => __( 'About navigation', 'fdmg-labels' ),
				'general_navigation'  => __( 'General navigation', 'fdmg-labels' ),
				'services_navigation' => __( 'Services navigation', 'fdmg-labels' ),
				'fdmg_navigation'     => __( 'FDMG navigation', 'fdmg-labels' ),
			)
		);
	}
}
