<?php

namespace ImpactInvestor\Search;

use WP_Query;

class Search {

	public function register_hooks(): void {
		add_filter( 'pre_get_posts', array( $this, 'search_only_show_posts' ) );
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
}
