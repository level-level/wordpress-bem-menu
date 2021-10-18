<?php

namespace Clarkson\WPadmin;

class Comments {
	public function register_hooks(): void {
		add_action( 'admin_init', array( $this, 'remove_comments_post_type' ) );
		add_action( 'admin_menu', array( $this, 'remove_comments_admin_menu' ) );
		add_action( 'init', array( $this, 'remove_comments_admin_bar' ) );
		add_filter( 'comments_open', array( $this, 'disable_comments_status' ), 20, 2 );
		add_filter( 'pings_open', array( $this, 'disable_comments_status' ), 20, 2 );
		add_filter( 'comments_array', array( $this, 'hide_existing_comments' ), 10, 1 );
	}

	/**
	 * Remove comment post types and metaboxes
	 */
	public function remove_comments_post_type(): void {
		global $pagenow;
		$post_types = get_post_types();

		foreach ( $post_types as $post_type ) {
			if ( is_string( $post_type ) ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}
		}

		// Remove comments metabox from dashboard
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );

		// Redirect any user trying to access comments page
		if ( 'edit-comments.php' === $pagenow ) {
			wp_safe_redirect( admin_url() );
			exit;
		}
	}

	public function remove_comments_admin_menu(): void {
		remove_menu_page( 'edit-comments.php' );
	}

	public function remove_comments_admin_bar(): void {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	public function disable_comments_status(): bool {
		return false;
	}

	public function hide_existing_comments(): array {
		return array();
	}
}
