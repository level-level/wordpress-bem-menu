<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson\Theme\PostTypes\Team;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\post;
use WP_Post;
use WP_Query;

class ii_team extends base_object {
	public static $type = Team::POST_TYPE_NAME;

	/**
	 * Cached version of the current paginated posts query.
	 * @var null|WP_Query
	 */
	public ?WP_Query $current_paginated_posts_query = null;

	/**
	 * Get posts for the current author
	 * @param array<string,mixed> $query_args
	 * @return array<post>
	 */
	public function get_posts( array $query_args = array() ): array {
		$query_args['no_found_rows'] = false;
		$query                       = $this->get_posts_query( $query_args );
		if ( ! $query->have_posts() ) {
			return array();
		}

		/** @var array<WP_Post> */
		$posts = $query->posts;

		/** @var array<post> */
		return Objects::get_instance()->get_objects( $posts );
	}

	public function get_email_address(): string {
		return get_field( 'email_address', $this->get_id() ) ?: '';
	}

	public function get_linkedin(): string {
		return get_field( 'linkedin', $this->get_id() ) ?: '';
	}

	public function get_location(): string {
		return get_field( 'location', $this->get_id() ) ?: '';
	}

	/**
	 * Get the current paginated posts as an array of post objects.
	 *
	 * @return array<post>
	 */
	public function get_current_paginated_posts(): array {
		$query = $this->get_current_paginated_posts_query();
		if ( ! $query->have_posts() ) {
			return array();
		}

		/** @var array<WP_Post> */
		$posts = $query->posts;

		/** @var array<post> */
		return Objects::get_instance()->get_objects( $posts );
	}

	/**
	 * Get the WP_Query used to get current paginated posts.
	 */
	private function get_current_paginated_posts_query(): WP_Query {
		if ( $this->current_paginated_posts_query ) {
			return $this->current_paginated_posts_query;
		}
		$args = array(
			'orderby'             => 'date',
			'order'               => 'DESC',
			'ignore_sticky_posts' => true,
			'posts_per_page'      => (int) get_option( 'posts_per_page' ) ?: 10,
			'paged'               => get_query_var( 'paged' ) ?: 1,
		);

		$this->current_paginated_posts_query = $this->get_posts_query( $args );
		return $this->current_paginated_posts_query;
	}

	/**
	 * Get the WP_Query to get posts.
	 * Please note this also retrieves posts
	 *
	 * @param array<string,mixed> $query_args
	 */
	private function get_posts_query( array $query_args = array() ): WP_Query {
		$args = wp_parse_args(
			$query_args, array(
				'post_type'   => PostTypesPost::POST_TYPE_NAME,
				'post_status' => 'publish',
				'meta_query'  => array(
					array(
						'key'     => PostTypesPost::META_AUTHORS,
						// ACF saves post ID's as a serialized array of strings
						'value'   => serialize( (string) $this->get_id() ), // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
						'compare' => 'LIKE',
					),
				),
			)
		);

		$args['fields'] = 'all';
		$wp_query       = new WP_Query( $args );
		return $wp_query;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_current_pagination_args(): array {
		$query = $this->get_current_paginated_posts_query();
		return array(
			'format'  => '?paged=%#%',
			'current' => $query->get( 'paged' ) ?: 1,
			'total'   => $query->max_num_pages,
		);
	}
}
