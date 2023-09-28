<?php
namespace FDMGLabels\StaticPages;

use FDMGLabels\StaticPages\Profile\ProfileController;
use WP_Query;

class Init {
	protected array $main_static_page_controllers;

	public function __construct() {
		$this->main_static_page_controllers = array(
			'profile'   => new ProfileController(),
		);
	}

	public function register_hooks(): void {
		add_filter( 'clarkson_context_args', array( $this, 'add_context_args' ) );
		add_action( 'template_redirect', array( $this, 'allow_pagination' ), 0 );

		foreach ( $this->main_static_page_controllers as $main_static_page_controller ) {
			$main_static_page_controller->register_hooks();
		}
	}

	public function add_context_args( array $context ): array {
		$context['static_pages'] = $this->main_static_page_controllers;
		return $context;
	}

	/**
	 * Prevent the WordPress canonical redirect if pagination is used on static pages.
	 * This allows us to use pagination parameters.
	 */
	public function allow_pagination(): void {
		global $wp_query;
		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		$paged = (int) $wp_query->get( 'paged' );
		foreach ( $this->main_static_page_controllers as $main_static_page_controller ) {
			if ( $paged > 1 && isset( $wp_query->query_vars[ $main_static_page_controller->get_query_var() ] ) ) {
				remove_action( 'template_redirect', 'redirect_canonical' );
				break;
			}
		}
	}
}
