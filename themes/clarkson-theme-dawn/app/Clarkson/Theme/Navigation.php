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
				'primary_navigation' => __( 'Primary Navigation', 'clarkson-theme-dawn' ),
				'legal_navigation'   => __( 'Legal Navigation', 'clarkson-theme-dawn' ),
			)
		);
	}
}
