<?php
namespace Clarkson\Theme\ACFPages;

class ThemeOptions {

	protected const FIELD_GROUP = 'theme_options';

	public const MENU_SLUG    = 'acf-options';
	public const META_OPTIONS = 'theme_options';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/widget-newsletter', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/block-advertisement', array( $this, 'add_context_args' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['options'] = get_field( self::META_OPTIONS, 'options' ) ?: array();
		return $context;
	}

	public function register(): void {
		acf_add_options_page(
			array(
				'menu_title' => _x( 'Theme options', 'Options', 'fdmg-labels' ),
				'page_title' => _x( 'Options', 'Options', 'fdmg-labels' ),
				'menu_slug'  => self::MENU_SLUG,
				'icon_url'   => 'dashicons-admin-settings',
				'redirect'   => false,
			)
		);
	}
}
