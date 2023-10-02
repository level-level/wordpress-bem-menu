<?php
namespace FDMGLabels\StaticPages;

use WP_Query;

/**
 * If you want to create a new static page with a new url structure, you should extend this class.
 * For example, if you want to create an url like https://example.test/profile.
 * You may also define routes inside the profile directory, like https://example.test/profile/edit.
 * This can be done here, but that's not ideal, as it causes extremely large controller files.
 * Instead, it's better to create a new controller file extending the AbstractChildController class.
 */
abstract class AbstractMainController extends AbstractController {
	public function register_hooks(): void {
		add_action( 'init', array( $this, 'add_rewrite_endpoint' ) );
		add_action( 'template_redirect', array( $this, 'handle_endpoint_request' ) );
	}

	abstract public function get_slug(): string;

	/**
	 * Get child controllers
	 *
	 * @return array<string,AbstractChildController>
	 */
	public function get_children(): array {
		return array();
	}

	public function get_permalink( string $sub_page = '' ): string {
		$relative_url = $this->get_slug();
		if ( ! empty( $sub_page ) ) {
			$relative_url .= '/' . ltrim( $sub_page, '/\\' );
		}
		$relative_url = user_trailingslashit( $relative_url );
		return home_url( $relative_url );
	}

	/**
	 * Get the ID of the current object
	 */
	public function get_current_object_id(): int {
		$object_id = 0;
		global $wp_query;
		if ( ! $wp_query instanceof WP_Query ) {
			return $object_id;
		}

		if ( ! isset( $wp_query->query_vars[ $this->get_query_var() ] ) ) {
			return 0;
		}

		return $this->extract_id_from_query_var( $wp_query->query_vars[ $this->get_query_var() ] );
	}

	/**
	 * Checks if the provided slug / one of the provided slugs is the current page.
	 *
	 * @param string|string[] $slug_or_slugs
	 * @return boolean
	 */
	public function is_current_page( string|array $slug_or_slugs ): bool {
		global $wp_query;
		if ( ! $wp_query instanceof WP_Query ) {
			return false;
		}

		if ( ! isset( $wp_query->query_vars[ $this->get_query_var() ] ) ) {
			return false;
		}

		$slugs = is_array( $slug_or_slugs ) ? $slug_or_slugs : array( $slug_or_slugs );
		foreach ( $slugs as $slug ) {
			$query_var = $wp_query->query_vars[ $this->get_query_var() ];
			$post_id   = $this->extract_id_from_query_var( $query_var );
			$query_var = $this->strip_id_from_query_var( $query_var, $post_id );
			if ( $query_var === $slug ) {
				return true;
			}
		}
		return false;
	}

	public function get_query_var(): string {
		return sprintf( 'fdmg-labels-staticpage-%s', $this->get_slug() );
	}

	public function add_rewrite_endpoint(): void {
		add_rewrite_endpoint( $this->get_slug(), EP_ROOT, $this->get_query_var() );
	}

	public function handle_endpoint_request(): void {
		global $wp_query;
		if ( ! $wp_query instanceof WP_Query ) {
			return;
		}

		if ( ! isset( $wp_query->query_vars[ $this->get_query_var() ] ) ) {
			return;
		}

		$query_var_value = $wp_query->query_vars[ $this->get_query_var() ];
		$this->handle_request( $query_var_value );

		// Trigger 404 if query var is set but cannot handle the request.
		$wp_query->posts = array();
		$wp_query->set_404();
		status_header( 404 );
	}

	abstract public function handle_request( string $query_var ): void;
}
