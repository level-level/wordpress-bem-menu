<?php

namespace Clarkson\WPadmin\Tools;

class Base {
	public function register_hooks(): void {
		add_action( 'wp_before_admin_bar_render', array( $this, 'add_main_menu_item' ), 10000 );
	}

	public function add_main_menu_item(): void {
		global $wp_admin_bar;
		$wp_admin_bar->add_node(
			array(
				'id'    => 'll-tools',
				'title' => 'Tools',
			)
		);
	}
}
