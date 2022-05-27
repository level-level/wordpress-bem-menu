<?php
namespace Clarkson\Utilities;

class Init {
	public function register_hooks(): void {
		add_filter( 'clarkson_context_args', array( $this, 'add_context_args' ) );
	}

	/**
	 * Make utilities available in the theme
	 */
	public function add_context_args( array $context ): array {
		$context['utilities'] = array(
			'i18n' => new I18n(),
		);
		return $context;
	}
}
