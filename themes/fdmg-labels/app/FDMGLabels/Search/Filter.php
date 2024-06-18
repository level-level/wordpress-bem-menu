<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\ACFPages\SearchOptions;
use Clarkson\Theme\PostTypes\Post;
use WP_Post;
use WP_Query;

class Filter {
	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		add_action( 'parse_query', array( $this, 'parse_query_post_date_min' ) );
		add_action( 'parse_query', array( $this, 'parse_query_post_date_max' ) );
		add_action( 'parse_query', array( $this, 'parse_query_post_author_id' ) );
		add_filter( 'ep_prepare_meta_allowed_keys', array( $this, 'elastic_index_meta' ), 10, 2 );
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
		$vars[] = 'author_id';
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
	 * When the query parameter author_id is provided,
	 * show posts that are published by specific authors.
	 */
	public function parse_query_post_author_id( WP_Query $query ): void {
		if ( ! $query->is_search ) {
			return;
		}

		$authors = $query->get( 'author_id' );

		if ( ! $authors ) {
			return;
		}

		if ( ! is_array( $authors ) ) {
			return;
		}

		$author_query = $query->get(
			'meta_query', array(
				'relation' => 'OR',
			)
		);

		foreach ( $authors as $author ) {
			$author_query[] = array(
				'key'     => Post::META_AUTHORS,
				'value'   => sprintf( '"%s"', $author ),
				'compare' => 'LIKE',
			);
		}

		$query->set( 'meta_query', $author_query );
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

	/**
	 * @return array<int, \Clarkson_Core\WordPress_Object\Clarkson_Object>
	 */
	public function get_filterable_authors(): array {
		$options          = get_field( SearchOptions::META_SEARCH_RESULTS, 'options' ) ?: array();
		$selected_authors = $options[ SearchOptions::META_SEARCH_RESULTS_AUTHORS ] ?? array();

		if ( ! $selected_authors ) {
			return array();
		}
		return $selected_authors;
	}

	/**
	 * Allow indexing post meta fields.
	 * This is needed for searching content inside the meta fields,
	 * as well as filtering by meta fields.
	 *
	 * @param array<string> $allowed_keys
	 * @param WP_Post $post
	 * @return array<string>
	 */
	public function elastic_index_meta( array $allowed_keys, WP_Post $post ): array {
		if ( $post->post_type === Post::POST_TYPE_NAME ) {
			$allowed_keys[] = Post::META_ALTERNATIVE_TITLE;
			$allowed_keys[] = Post::META_AUTHORS;
		}
		return $allowed_keys;
	}
}
