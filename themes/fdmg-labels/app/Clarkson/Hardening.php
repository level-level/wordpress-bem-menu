<?php

namespace Clarkson;

class Hardening {
	public function register_hooks(): void {
		add_filter( 'xmlrpc_enabled', '__return_false' );
		add_action( 'init', array( $this, 'reset_roles' ) );
	}

	/**
	 * When the version updates we need to reset roles, because
	 * WordPress saves these into the database.
	 */
	public function reset_roles(): void {
		$theme = wp_get_theme();
		if ( get_option( 'clarkson_theme_version' ) !== $theme->get( 'Version' ) || ( defined( 'WP_DEBUG' ) && (bool) constant( 'WP_DEBUG' ) ) ) {
			do_action( 'clarkson_update_capabilities' );
			update_option( 'clarkson_theme_version', $theme->get( 'Version' ) );
		};
	}
}
