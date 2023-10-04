<?php

namespace Clarkson\WPadmin;

class Darkmode {
	public function register_hooks(): void {
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_darkmode_toggle' ), 10000 );
	}

	/**
	 * Show darkmode toggle in admin bar on block editor pages
	 */
	public function add_darkmode_toggle(): void {
		global $wp_admin_bar;

		if ( ! is_admin() ) {
			return;
		}

		$current_screen = get_current_screen();
		if ( $current_screen instanceof \WP_Screen && ! $current_screen->is_block_editor() ) {
			return;
		}

		$wp_admin_bar->add_node(
			array(
				'id'    => 'll-darkmode',
				'title' => _x( 'Enable darkmode', 'Darkmode', 'kit-admin' ),
				'meta'  => array(
					'title' => _x( 'Disable darkmode', 'Darkmode', 'kit-admin' ),
				),
			)
		);
	}
}
