<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post;
use Clarkson_Core\WordPress_Object\category;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use WP_Post;

class ArticleGroup {

	public const SELECTION_TYPE_MANUAL    = 'manual';
	public const SELECTION_TYPE_AUTOMATIC = 'automatic';

	public const SORT_RECENT      = 'recent';
	public const SORT_MOST_VIEWED = 'most_viewed';

	/**
	 * ACF group fields data
	 *
	 * @var array<string, mixed>
	 */
	protected array $data;

	/**
	 * @param array<string,mixed> $data
	 */
	public function __construct( array $data ) {
		$this->data = $data;
	}

	/**
	 * @return self::SELECTION_TYPE_*
	 */
	public function get_selection_type(): string {
		$type = $this->data[ Newsletter::META_ARTICLE_GROUP_SELECTION_TYPE ] ?? self::SELECTION_TYPE_MANUAL;
		return empty( $type ) ? self::SELECTION_TYPE_MANUAL : $type;
	}

	/**
	 * @return self::SORT_*
	 */
	public function get_sort(): string {
		$sort = $this->data[ Newsletter::META_ARTICLE_GROUP_SORT ] ?? self::SORT_RECENT;
		return empty( $sort ) ? self::SORT_RECENT : $sort;
	}

	public function get_article_count(): int {
		return (int) ( $this->data[ Newsletter::META_ARTICLE_GROUP_ARTICLE_COUNT ] ?? 0 );
	}

	public function get_article_age(): int {
		return (int) ( $this->data[ Newsletter::META_ARTICLE_GROUP_ARTICLE_AGE ] ?? 0 );
	}

	/**
	 * @return category[]
	 */
	public function get_filter_by_categories(): array {
		$categories = $this->data[ Newsletter::META_ARTICLE_GROUP_FILTER_CATEGORY ] ?? array();
		return is_array( $categories ) ? $categories : array();
	}

	/**
	 * @return fdmg_genre[]
	 */
	public function get_filter_by_genres(): array {
		$genres = $this->data[ Newsletter::META_ARTICLE_GROUP_FILTER_GENRE ] ?? array();
		return is_array( $genres ) ? $genres : array();
	}

	/**
	 * @return fdmg_section[]
	 */
	public function get_filter_by_sections(): array {
		$sections = $this->data[ Newsletter::META_ARTICLE_GROUP_FILTER_SECTION ] ?? array();
		return is_array( $sections ) ? $sections : array();
	}

	/**
	 * Get the articles that should be displayed in the newsletter
	 *
	 * @return int[]
	 */
	public function get_article_ids(): array {
		$article_ids = array();
		switch ( $this->get_selection_type() ) {
			case self::SELECTION_TYPE_AUTOMATIC:
				$article_ids = $this->get_automatic_article_ids();
				break;
			default:
				$article_ids = $this->get_manual_article_ids();
				break;
		}
		return $article_ids;
	}

	/**
	 * Get the manually selected articles that should be displayed in the newsletter
	 *
	 * @return int[]
	 */
	protected function get_manual_article_ids(): array {
		$articles = $this->data[ Newsletter::META_ARTICLE_GROUP_ARTICLES ] ?? array();
		$articles = is_array( $articles ) ? $articles : array();

		$article_ids = array_map(
			function( array $articles_row ): int {
				return (int) ( $articles_row[ Newsletter::META_ARTICLE_GROUP_ARTICLE_ARTICLE ] ?? 0 );
			}, $articles
		);

		return array_filter( $article_ids );
	}

	/**
	 * Get the automatically selected articles that should be displayed in the newsletter
	 *
	 * @return int[]
	 */
	protected function get_automatic_article_ids(): array {
		$article_ids = array();
		switch ( $this->get_sort() ) {
			case self::SORT_MOST_VIEWED:
				$article_ids = $this->get_automatic_most_viewed_article_ids();
				break;
			default:
				$article_ids = $this->get_automatic_most_recent();
				break;
		}
		return $article_ids;
	}

	/**
	 * Get the automatically selected most recent articles
	 *
	 * @return int[]
	 */
	protected function get_automatic_most_recent(): array {
		$allowed_terms = array_merge(
			$this->get_filter_by_categories(),
			$this->get_filter_by_genres(),
			$this->get_filter_by_sections()
		);

		$tax_query = array(
			'relation' => 'OR',
		);
		foreach ( $allowed_terms as $allowed_term ) {
			$tax_query[] = array(
				'taxonomy' => $allowed_term->get_taxonomy(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $allowed_term->get_term_taxonomy_id(),
			);
		}

		$args = array(
			'posts_per_page' => $this->get_article_count(),
			'post_type'      => Post::POST_TYPE_NAME,
			'post_status'    => 'publish',
			'orderby'        => array(
				'date' => 'DESC',
				'ID'   => 'DESC',
			),
			'fields'         => 'ids',
			'date_query'     => array(
				array(
					'column' => 'post_date_gmt',
					'after'  => $this->get_article_age() . ' days ago',
				),
			),
			'tax_query'      => $tax_query,

		);

		/** @var int[] */
		$post_ids = get_posts( $args );
		return $post_ids;
	}

	/**
	 * Get the automatically selected most viewed articles.
	 * Koko Analyics does not implement a a lot of features for filtering posts of the query.
	 * So instead, we first use Koko analytics to give the order of most viewed articles.
	 * Then, we use a WP_Query to get the posts, but filtered, in the same order.
	 *
	 * @return int[]
	 */
	protected function get_automatic_most_viewed_article_ids(): array {
		// Get post ids ordered by most viewed
		$args = array(
			'number'    => $this->get_article_count(),
			'post_type' => Post::POST_TYPE_NAME,
			'days'      => $this->get_article_age(),
		);

		if ( ! function_exists( '\KokoAnalytics\get_most_viewed_posts' ) ) {
			return array();
		}

		$posts    = \KokoAnalytics\get_most_viewed_posts( $args );
		$post_ids = array_map(
			function( WP_Post $post ): int {
				return $post->ID;
			}, $posts
		);

		// Filter the post ids by the allowed terms
		$allowed_terms = array_merge(
			$this->get_filter_by_categories(),
			$this->get_filter_by_genres(),
			$this->get_filter_by_sections()
		);

		$tax_query = array(
			'relation' => 'OR',
		);
		foreach ( $allowed_terms as $allowed_term ) {
			$tax_query[] = array(
				'taxonomy' => $allowed_term->get_taxonomy(),
				'field'    => 'term_taxonomy_id',
				'terms'    => $allowed_term->get_term_taxonomy_id(),
			);
		}

		$args = array(
			'posts_per_page'      => $this->get_article_count(),
			'post_type'           => Post::POST_TYPE_NAME,
			'post_status'         => 'publish',
			'post__in'            => $post_ids,
			'ignore_sticky_posts' => true,
			'orderby'             => 'post__in', // This keeps the same order as Koko Analytics
			'fields'              => 'ids',
			'tax_query'           => $tax_query,
		);

		/** @var int[] */
		$post_ids = get_posts( $args );
		return $post_ids;
	}

}
