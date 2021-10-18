<?php
namespace Clarkson\Theme;

class Options {

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'add_options_pages' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	public function add_options_pages(): void {
		acf_add_options_page(
			array(
				'menu_title' => _x( 'Theme options', 'Options', 'impact-investor' ),
				'page_title' => _x( 'Options', 'Options', 'impact-investor' ),
				'menu_slug'  => 'acf-options',
				'icon_url'   => 'dashicons-admin-settings',
			)
		);
	}

	/**
	* Make global options available in the theme
	*/
	public function add_context_args( array $context ): array {
		$context['options'] = get_field( 'theme_options', 'options' ) ?: array();
		$context['socials'] = get_option( 'wpseo_social' ) ?: array();
		return $context;
	}
}
