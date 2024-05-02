<?php

namespace FDMGLabels\Search;

use Clarkson\Theme\ACFPages\SearchOptions;
use WP_Query;

class Search {
	public function register_hooks(): void {
		add_filter( 'pre_get_posts', array( $this, 'search_only_show_posts' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	/**
	 * Only show posts on the search results page
	 */
	public function search_only_show_posts( WP_Query $query ): WP_Query {
		if ( ! is_admin() && $query->is_main_query() ) {
			if ( $query->is_search ) {
				$query->set( 'post_type', 'post' );
			}
		}

		return $query;
	}

	/**
	 * Add context arguments for search page
	 *
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		// Add global context data
		$search_options = get_field( SearchOptions::META_SEARCH_RESULTS, 'options' ) ?: array();
		$context['default_sort']   = $search_options[ SearchOptions::META_SEARCH_DEFAULT_SORTING ] ?? 'relevance';
		$context['default_period'] = $search_options[ SearchOptions::META_SEARCH_DEFAULT_MIN_POST_DATE ] ?? '3-years';

		// If not a search page, return context
		if ( ! is_search() ) {
			return $context;
		}

		// Add search context data
		$context['search_query'] = get_search_query();
		return $context;
	}
}
