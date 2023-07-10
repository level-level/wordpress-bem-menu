<?php
namespace Clarkson\Theme;

class Options {

	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/widget-newsletter', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/block-advertisement', array( $this, 'add_context_args' ) );
	}

	/**
	 * Make global options available in the theme
	 *
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['socials'] = get_option( 'wpseo_social' ) ?: array();
		return $context;
	}
}
