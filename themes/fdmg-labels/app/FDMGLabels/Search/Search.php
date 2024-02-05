<?php

namespace FDMGLabels\Search;

use WP_Query;

class Search {

	public function register_hooks(): void {
		add_filter( 'pre_get_posts', array( $this, 'search_only_show_posts' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_elasticsearch_to_context' ), 10, 2 );
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
