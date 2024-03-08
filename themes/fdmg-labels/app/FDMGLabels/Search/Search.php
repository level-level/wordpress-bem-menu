<?php

namespace FDMGLabels\Search;

use Clarkson\Theme\ACFPages\SearchOptions;
use WP_Query;

class Search {
	public function register_hooks(): void {
		add_filter( 'pre_get_posts', array( $this, 'search_only_show_posts' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_elasticsearch_to_context' ), 10, 2 );
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
	 *
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$search_options = get_field( SearchOptions::META_SEARCH_RESULTS, 'options' ) ?: array();

		$context['default_sort']   = $search_options[ SearchOptions::META_SEARCH_DEFAULT_SORTING ] ?? 'relevance';
		$context['default_period'] = $search_options[ SearchOptions::META_SEARCH_DEFAULT_MIN_POST_DATE ] ?? '3-years';

		return $context;
	}

	/**
	 * If search query is performed, add variable to context which determines if Elasticsearch was used
	 *
	 * @param array<string,mixed> $context
	 * @param WP_Query $query
	 * @return array<string,mixed>
	 */
	public function add_elasticsearch_to_context( array $context, WP_Query $query ): array {
		if ( $query->is_search() ) {
			$context['is_elasticsearch_result'] = $query->elasticsearch_success ?? false;
		}
		return $context;
	}
}
