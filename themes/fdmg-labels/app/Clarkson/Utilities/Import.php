<?php
namespace Clarkson\Utilities;

class Import {

	/**
	 * Prepare for an import
	 *
	 * @return void
	 */
	public function setup(): void {
		if ( ! defined( 'WP_IMPORTING' ) ) {
			define( 'WP_IMPORTING', true );
		}

		// Modify filters
		if ( function_exists( 'FWP' ) ) {
			remove_action( 'save_post', array( FWP()->indexer, 'save_post' ) );
		}
		add_filter(
			'ep_indexable_sites', function () {
				return array();
			}
		);
		add_filter( 'searchwp\index\process\enabled', '__return_false' );

		// Defer counting
		wp_defer_term_counting( true );
		wp_defer_comment_counting( true );

		// Flush cache
		wp_cache_flush();
	}

	/**
	 * Cleanup after the import
	 *
	 * @return void
	 */
	public function shutdown(): void {
		// Stop defer counting
		wp_defer_term_counting( false );
		wp_defer_comment_counting( false );

		// Flush cache
		wp_cache_flush();
	}

	/**
	 * Clear memory during import
	 *
	 * @return void
	 */
	public function clear_memory(): void {
		global $wpdb, $wp_object_cache, $wp_actions;

		$wpdb->queries = array();
		$wp_actions    = array(); //phpcs:ignore
		if ( ! is_object( $wp_object_cache ) ) {
			return;
		}
		$wp_object_cache->group_ops      = array();
		$wp_object_cache->stats          = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache          = array();
		if ( is_callable( array( $wp_object_cache, '__remoteset' ) ) ) {
			$wp_object_cache->__remoteset();
		}
	}
}
