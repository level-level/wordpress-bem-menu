<?php
namespace FDMGLabels\StaticPages;

use WP_Query;

/**
 * If you want to create a sub path of a static page, you should extend this class.
 * For example, if you want to create an url like https://example.test/profile/edit.
 * You should then create a new controller file extending this class.
 */
abstract class AbstractChildController extends AbstractController {

	protected AbstractMainController $parent_controller;

	public function __construct( AbstractMainController $parent_controller ) {
		parent::__construct();
		$this->parent_controller = $parent_controller;
	}

	abstract public function handle_request( string $query_var ): void;

	public function get_permalink( string $sub_page = '' ): string {
		$relative_url = $this->parent_controller->get_slug();
		if ( ! empty( $sub_page ) ) {
			$relative_url .= '/' . ltrim( $sub_page, '/\\' );
		}
		$relative_url = user_trailingslashit( $relative_url );
		return home_url( $relative_url );
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

		if ( ! isset( $wp_query->query_vars[ $this->parent_controller->get_query_var() ] ) ) {
			return false;
		}

		$slugs = is_array( $slug_or_slugs ) ? $slug_or_slugs : array( $slug_or_slugs );
		foreach ( $slugs as $slug ) {
			$query_var = $wp_query->query_vars[ $this->parent_controller->get_query_var() ];
			$post_id   = $this->extract_id_from_query_var( $query_var );
			$parent_id = $this->extract_parent_id_from_query_var( $query_var );
			$query_var = ! empty( $parent_id ) ? $this->strip_ids_from_query_var( $query_var, $post_id, $parent_id ) : $this->strip_id_from_query_var( $query_var, $post_id );
			if ( $query_var === $slug ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Remove the post ID and parent ID from the query var. If not exists, nothing is removed.
	 */
	public function strip_ids_from_query_var( string $query_var, int $post_id, int $parent_id ): string {
		if ( empty( $parent_id ) ) {
			$search = sprintf( '/%s', $post_id );
		} else {
			$search = sprintf( '/%s-%s', $post_id, $parent_id );
		}
		return str_replace( $search, '', $query_var );
	}

	/**
	 * Get the related parent ID from the query var, or 0 if not exists.
	 */
	public function extract_parent_id_from_query_var( string $query_var ): int {
		$matches = array();
		preg_match( '/(\-\d+)/', $query_var, $matches );
		if ( ! empty( $matches[0] ) ) {
			return (int) str_replace( '-', '', $matches[0] );
		}
		return 0;
	}
}
