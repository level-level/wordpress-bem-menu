<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\ACFPages\SearchOptions;
use WP_Query;

class Filter {
	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_query', array( $this, 'parse_query_post_date_min' ) );
		add_action( 'parse_query', array( $this, 'parse_query_post_date_max' ) );
	}

	/**
	 * @param array<mixed, mixed> $context
	 * @return array<mixed, mixed>
	 */
	public function add_context_args( array $context ): array {
		if ( ! is_search() ) {
			return $context;
		}

		$context['filter'] = $this;
		return $context;
	}

	public function get_filter_value( string $query_var, ?WP_Query $query = null ): mixed {
		global $wp_query;
		$query = $query ?: $wp_query;
		if ( ! $query instanceof WP_Query ) {
			return null;
		}

		return $query->query[ $query_var ] ?? get_query_var( $query_var );
	}

	/**
	 * Register custom query vars
	 * @param array<mixed, mixed> $vars
	 * @return array<mixed, mixed>
	 */
	public function add_query_vars( array $vars ): array {
		$vars[] = 'post_date_min';
		$vars[] = 'post_date_max';
		$vars[] = 'period';
		return $vars;
	}

	/**
	 * When the query parameter post_date_min is provided,
	 * show posts that are published on or after the provided date.
	 */
	public function parse_query_post_date_min( WP_Query $query ): void {
		if ( ! $query->is_search ) {
			return;
		}

		$post_date_min = $query->get( 'post_date_min' );
		if ( ! $post_date_min ) {
			return;
		}

		if ( ! is_string( $post_date_min ) || ! strtotime( $post_date_min ) ) {
			return;
		}

		$date_query              = $query->get( 'date_query', array() );
		$date_query['inclusive'] = true;
		$date_query['after']     = $post_date_min;
		$query->set( 'date_query', $date_query );
	}

	/**
	 * When the query parameter post_date_max is provided,
	 * show posts that are published on or before the provided date.
	 */
	public function parse_query_post_date_max( WP_Query $query ): void {
		if ( ! $query->is_search ) {
			return;
		}

		$post_date_max = $query->get( 'post_date_max' );

		if ( ! $post_date_max ) {
			return;
		}

		if ( ! is_string( $post_date_max ) || ! strtotime( $post_date_max ) ) {
			return;
		}

		$date_query              = $query->get( 'date_query', array() );
		$date_query['inclusive'] = true;
		$date_query['before']    = $post_date_max;
		$query->set( 'date_query', $date_query );
	}

	/**
	 * @return array<int, \Clarkson_Core\WordPress_Object\Clarkson_Term>
	 */
	public function get_filterable_terms( string $taxonomy ): array {
		$options = get_field( SearchOptions::META_SEARCH_RESULTS, 'options' );

		if ( ! $options ) {
			return array();
		}

		if ( ! array_key_exists( $taxonomy, $options ) ) {
			return array();
		}

		$terms = $options[ $taxonomy ];

		if ( ! $terms ) {
			return array();
		}

		return $terms;
	}
}
