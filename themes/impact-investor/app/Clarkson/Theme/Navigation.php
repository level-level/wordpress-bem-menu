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
				'primary_navigation'       => __( 'Primary navigation', 'impact-investor' ),
				'first_footer_navigation'  => __( 'First footer navigation', 'impact-investor' ),
				'second_footer_navigation' => __( 'Second footer navigation', 'impact-investor' ),
				'third_footer_navigation'  => __( 'Third footer navigation', 'impact-investor' ),
				'fourth_footer_navigation' => __( 'Fourth footer navigation', 'impact-investor' ),
			)
		);
	}
}
