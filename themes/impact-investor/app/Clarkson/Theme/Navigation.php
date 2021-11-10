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
				'primary_navigation'  => __( 'Primary navigation', 'impact-investor' ),
				'about_navigation'    => __( 'About navigation', 'impact-investor' ),
				'general_navigation'  => __( 'General navigation', 'impact-investor' ),
				'services_navigation' => __( 'Services navigation', 'impact-investor' ),
				'fdmg_navigation'     => __( 'FDMG navigation', 'impact-investor' ),
			)
		);
	}
}
