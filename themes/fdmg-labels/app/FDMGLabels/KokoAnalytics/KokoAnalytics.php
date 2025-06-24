<?php
namespace FDMGLabels\KokoAnalytics;

use Clarkson\Theme\PostTypes\Post as PostTypePost;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\post;
use WP_Post;

class KokoAnalytics {

	/**
	 * Get the most most read posts that have been published in the last x days.
	 *
	 * @param int $days The number of days to look back.
	 * @param int $amount The number of posts to return.
	 * @return post[] An array of Post objects.
	 */
	public function get_most_viewed_articles_published_in_last_x_days( int $days, int $amount ): array {
		$ids = $this->get_most_viewed_article_ids_published_in_last_x_days( $days, $amount );
		if ( empty( $ids ) ) {
			return array();
		}

		/**
		 * Get the posts by IDs.
		 * We use 'post__in' to ensure that the posts are returned in the order of the IDs.
		 *
		 * @var WP_Post[] $posts
		 */
		$posts = get_posts(
			array(
				'post_type'           => PostTypePost::POST_TYPE_NAME,
				'post_status'         => 'publish',
				'numberposts'         => $amount,
				'post__in'            => $ids,
				'orderby'             => 'post__in', // Ensure the order of posts matches
				'ignore_sticky_posts' => true,
			)
		);

		/**
		 * @var post[] $objects
		 */
		$objects = Objects::get_instance()->get_objects( $posts );
		return $objects;
	}

	/**
	 * Get the most most read posts ids that have been published in the last x days.
	 *
	 * @param int $days The number of days to look back.
	 * @param int $amount The number of post IDs to return.
	 * @return int[] An array of post IDs.
	 */
	private function get_most_viewed_article_ids_published_in_last_x_days( int $days, int $amount ): array {
		/**
		 * Make list of allowed post ids, which are all posts published in the last X days.
		 * This is necessary because Koko Analytics returns the most viewed posts in the last X days, but including all posts.
		 *
		 * @var int[] $allowed_post_ids
		 */
		$allowed_post_ids = get_posts(
			array(
				'numberposts'         => 200, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_numberposts
				'post_type'           => PostTypePost::POST_TYPE_NAME,
				'post_status'         => 'publish',
				'fields'              => 'ids',
				'ignore_sticky_posts' => true,
				'date_query'          => array(
					array(
						'after' => sprintf( '-%d days', $days ),
					),
				),
			)
		);
		if ( empty( $allowed_post_ids ) ) {
			return array();
		}

		/**
		 * Loop and for every iteration, get a paginated list of most viewed posts.
		 * If the list is empty, or we have reached the amount of posts to return, we stop.
		 */
		$max_loops  = 10;
		$page       = 1;
		$post_ids   = array();
		$batch_size = min( $amount * 2, 100 ); // Number of posts to fetch per page.
		while ( $page < $max_loops ) {
			if ( count( $post_ids ) >= $amount ) {
				break; // Already have enough posts, exit the loop.
			}

			$most_viewed_post_ids = $this->get_most_viewed_post_ids_in_past_days( $days, $batch_size, $page );
			if ( empty( $most_viewed_post_ids ) ) {
				break; // No more posts found, exit the loop.
			}

			// Filter the most viewed posts to only include those that are in the allowed post IDs.
			$filtered_post_ids = array_intersect( $allowed_post_ids, $most_viewed_post_ids );
			$filtered_post_ids = array_values( $filtered_post_ids );
			$post_ids          = array_merge( $post_ids, $filtered_post_ids );

			++$page;
		}
		return array_slice( $post_ids, 0, $amount );
	}

	/**
	 * Returns the most viewed posts IDS in the past X days.
	 *
	 * @param int $days  The number of days to look back.
	 * @param int $amount The number of posts to return.
	 * @param int $page  The page number for pagination.
	 *
	 * @return int[] An array of post IDs.
	 */
	private function get_most_viewed_post_ids_in_past_days( int $days, int $amount, int $page = 1 ): array {
		if ( ! is_plugin_active( 'koko-analytics/koko-analytics.php' ) ) {
			return array();
		}

		if ( ! function_exists( '\KokoAnalytics\get_most_viewed_post_ids' ) ) {
			return array();
		}

		$args     = array(
			'number'    => $amount,
			'paged'     => $page - 1,
			'post_type' => PostTypePost::POST_TYPE_NAME,
			'days'      => $days,
		);
		$post_ids = \KokoAnalytics\get_most_viewed_post_ids( $args );
		return array_map( 'intval', $post_ids );
	}
}
