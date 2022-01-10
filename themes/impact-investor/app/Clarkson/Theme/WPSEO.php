<?php
namespace Clarkson\Theme;

class WPSEO {
	public function register_hooks(): void {
		add_filter( 'pre_get_document_title', array( $this, 'filter_wpseo_title' ), 10, 2 );
		add_filter( 'wpseo_title', array( $this, 'filter_wpseo_title' ), 10, 1 );
	}

	/**
	 * Change the title of the search results page and make it translatable
	 * Correct format: "Search results for (Search phrase), page (Pagenumber) of (Pagetotal) (Seperator) (Site title)"
	 */
	public function filter_wpseo_title( string $title ): string {
		global $wp_query;

		if ( is_search() ) {
			$page        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
			$pages_total = $wp_query->max_num_pages;
			$search_term = get_search_query();
			$separator   = is_plugin_active( 'wordpress-seo/wp-seo.php' ) || is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ? YoastSEO()->helpers->options->get_title_separator() : '-';
			$title       = sprintf( __( 'Search results for "%s"', 'clarkson-theme-dawn' ), $search_term ) . ', ' . sprintf( __( 'page %1$s of %2$s', 'clarkson-theme-dawn' ), $page, $pages_total ) . ' ' . $separator . ' ' . get_bloginfo( 'name' );
		}
		return $title;
	}
}
