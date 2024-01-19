<?php

namespace Clarkson\WPadmin\Tools;

class Styleguide {

	public const NAME = 'Styleguide';
	public const SLUG = 'll-tools-styleguide';

	public function register_hooks(): void {
		if ( ! file_exists( get_stylesheet_directory() . '/storybook-static/index.html' ) ) {
			return;
		}

		add_action( 'wp_before_admin_bar_render', array( $this, 'add_menu_bar_item' ), 10000 );
		add_action( 'admin_menu', array( $this, 'add_management_page' ) );
	}

	public function add_management_page(): void {
		add_management_page( self::NAME, self::NAME, 'manage_options', self::SLUG, array( $this, 'render_page' ) );
	}

	public function add_menu_bar_item(): void {
		global $wp_admin_bar;
		$wp_admin_bar->add_node(
			array(
				'id'     => self::SLUG,
				'parent' => 'll-tools',
				'title'  => self::NAME,
				'href'   => admin_url( 'tools.php?page=' . self::SLUG ),
			)
		);
	}

	public function render_page(): void {
		echo "<iframe style='width: 100%; height: 100vh; margin-left: -20px;'
			src='" . esc_html( get_stylesheet_directory_uri() . '/storybook-static/index.html' ) . "'></iframe>";
	}

	/**
	 * Enable styleguide only for local and staging (admin only) environment
	 */
	public static function is_enabled(): bool {
		$environment = wp_get_environment_type();

		if ( $environment === 'production' ) {
			return false;
		}

		if ( $environment === 'staging' && ! current_user_can( 'delete_others_pages' ) ) {
			return false;
		}

		return true;
	}
}
