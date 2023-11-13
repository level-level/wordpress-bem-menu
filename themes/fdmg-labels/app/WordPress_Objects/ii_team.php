<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson\Theme\PostTypes\Team;
use Clarkson_Core\WordPress_Object\post;

class ii_team extends base_object {
	public static $type = Team::POST_TYPE_NAME;

	/**
	 * @var null|array<post>
	 */
	public ?array $author_posts = null;

	/**
	 * @var WP_Query
	 */
	public ?\WP_Query $author_query = null;

	private function get_post_ids_by_author( int $amount = -1 ): array {
		$args = array(
			'post_type'      => PostTypesPost::POST_TYPE_NAME,
			'posts_per_page' => $amount,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'authors',
					'value'   => $this->get_id(),
					'compare' => 'LIKE',
				),
			),
		);

		return get_posts( $args );
	}

	/**
	 * Get posts for the current author
	 * @return null|array<post>
	 */
	public function get_posts(): ?array {
		if ( $this->author_posts === null ) {
			$this->set_posts_and_query();
		}
		return $this->author_posts;
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
	 * Set the author_posts and author_query for this author object
	*/
	private function set_posts_and_query(): void {
		$post_ids       = $this->get_post_ids_by_author();
		$posts_per_page = (int) get_option( 'posts_per_page' ) ?: 10;

		$wp_query = new \WP_Query(
			array(
				'post_type'           => PostTypesPost::POST_TYPE_NAME,
				'post_status'         => 'publish',
				'orderby'             => 'date',
				'order'               => 'DESC',
				'no_found_rows'       => false,
				'posts_per_page'      => $posts_per_page,
				'paged'               => get_query_var( 'paged' ) ?: 1,
				'post__in'            => empty( $post_ids ) ? array( -1 ) : $post_ids, // Provide array( -1 ) as fallback so WP doesn't start retrieving posts
				'ignore_sticky_posts' => 1,
			)
		);

		$this->author_query = $wp_query;

		$posts = array();
		foreach ( $wp_query->posts as $wp_post ) {
			if ( $wp_post instanceof \WP_Post ) {
				$post    = new post( $wp_post );
				$posts[] = $post;
			}
		}
		$this->author_posts = $posts;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_pagination_args(): array {
		if ( $this->author_posts === null || $this->author_query === null ) {
			$this->set_posts_and_query();
		}

		$permalink = $this->get_permalink();

		if ( ! $permalink ) {
			$permalink = '';
		}

		return array(
			'base'    => $permalink . '%_%',
			'total'   => $this->author_query->max_num_pages ?? 0,
			'current' => $this->author_query->query_vars['paged'] ?? 1,
		);
	}
}
