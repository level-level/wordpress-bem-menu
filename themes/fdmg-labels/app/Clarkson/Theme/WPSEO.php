<?php
namespace Clarkson\Theme;

class WPSEO {
	public function register_hooks(): void {
		add_filter( 'pre_get_document_title', array( $this, 'filter_wpseo_title' ) );
		add_filter( 'wpseo_title', array( $this, 'filter_wpseo_title' ) );
	}

	public function filter_wpseo_title( string $title ): string {
		global $wp_query;

		if ( ! is_search() ) {
			return $title;
		}

		$page        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$pages_total = $wp_query->max_num_pages;
		$search_term = get_search_query();
		$separator   = function_exists( 'YoastSEO' ) ? YoastSEO()->helpers->options->get_title_separator() : '-';
		$title       = sprintf( _x( 'Search results for "%1$s", page %2$s of %3$s', 'SEO', 'fdmg-labels' ), $search_term, $page, $pages_total );

		return $title . ' ' . $separator . ' ' . get_bloginfo( 'name' );
	}
}
