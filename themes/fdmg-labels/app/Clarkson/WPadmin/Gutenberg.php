<?php
namespace Clarkson\WPadmin;

/***
 *
 * Configure Gutenberg restrictions within wp-admin
 *
 **/

class Gutenberg {

	public function register_hooks(): void {
		add_action( 'after_setup_theme', array( $this, 'supported_theme_features' ) );
	}

	public function supported_theme_features(): void {
		remove_theme_support( 'core-block-patterns' );
		remove_theme_support( 'block-templates' );

		add_theme_support( 'align-wide' );
		add_theme_support(
			'editor-font-sizes',
			array(
				array(
					'name' => _x( 'Intro', 'Block', 'fdmg-labels' ),
					'size' => 28,
					'slug' => 'intro',
				),
			)
		);
	}
}
