<?php
namespace Clarkson\Theme;

class WPSEO {
	public function register_hooks(): void {
		add_filter( 'pre_get_document_title', array( $this, 'filter_wpseo_search_title' ), 10, 1 );
		add_filter( 'wpseo_title', array( $this, 'filter_wpseo_search_title' ), 10, 1 );
		add_filter( 'wpseo_breadcrumb_separator', array( $this, 'change_breadcrumb_separator' ) );
		add_filter( 'wpseo_breadcrumb_output_wrapper', array( $this, 'filter_wpseo_breadcrumb_output_wrapper' ), 10 );
		add_filter( 'wpseo_breadcrumb_output_class', array( $this, 'filter_wpseo_breadcrumb_output_class' ), 10 );
		add_filter( 'wpseo_breadcrumb_single_link_wrapper', array( $this, 'filter_wpseo_breadcrumb_single_link_wrapper' ), 10 );
		add_filter( 'wpseo_breadcrumb_single_link', array( $this, 'filter_wpseo_breadcrumb_single_link' ), 10, 1 );
		add_filter( 'wpseo_exclude_from_sitemap_by_post_ids', array( $this, 'exclude_pages_from_xml_sitemaps' ) );
	}

	/**
	 * Change the title of the search results page and make it translatable
	 * Correct format: "Search results for (Search phrase), page (Pagenumber) of (Pagetotal) (Separator) (Site title)"
	 */
	public function filter_wpseo_search_title( string $title ): string {
		global $wp_query;

		if ( ! is_search() ) {
			return $title;
		}

		$page        = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
		$pages_total = $wp_query->max_num_pages === 0 ? 1 : $wp_query->max_num_pages;
		$search_term = get_search_query();
		$separator   = function_exists( 'YoastSEO' ) ? YoastSEO()->helpers->options->get_title_separator() : '-';
		$title       = sprintf( _x( 'Search results for "%1$s", page %2$s of %3$s', 'Search', 'fdmg-labels-admin' ), $search_term, $page, $pages_total );

		return $title . ' ' . $separator . ' ' . get_bloginfo( 'name' );
	}

	public function change_breadcrumb_separator(): string {
		return '<li><span class="icon" aria-hidden="true">' . SVGIcons::get_instance()->get( 'arrow-right' ) . '</span></li>';
	}

	public function filter_wpseo_breadcrumb_output_class(): string {
		return 'breadcrumbs';
	}

	public function filter_wpseo_breadcrumb_output_wrapper(): string {
		return 'ul';
	}

	public function filter_wpseo_breadcrumb_single_link_wrapper(): string {
		return 'li';
	}

	public function filter_wpseo_breadcrumb_single_link( string $link ): string {
		return str_replace( '<a', '<a class="breadcrumbs__link"', $link );
	}

	/**
	 * @return array<mixed>
	 */
	public function exclude_pages_from_xml_sitemaps(): array {
		$exclude_pages      = array();
		$selected_home_page = get_option( 'page_on_front', false );
		$selected_404_page  = get_option( 'clarkson-page-for-404', false );

		if ( $selected_404_page ) {
			$exclude_pages[] = $selected_404_page;
		}

		if ( $selected_home_page ) {
			$exclude_pages[] = $selected_home_page;
		}

		return $exclude_pages;
	}
}
