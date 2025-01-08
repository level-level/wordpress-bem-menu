<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\ArticlesByAuthor;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\WordPress_Object\post;

class acf_widget_articles_by_author extends base_block {

	/**
	 * @return array<int, \Clarkson_Core\WordPress_Object\Clarkson_Object>
	 */
	public function get_articles_by_author(): array {
		$amount = $this->content_attributes[ ArticlesByAuthor::WIDGET_META_AMOUNT ] ?? 5;
		$post   = $this->get_object();
		if ( ! $post instanceof post ) {
			return array();
		}

		$authors = $post->get_authors();
		$author  = array_shift( $authors );
		if ( ! $author ) {
			return array();
		}

		$author_id   = $author->get_id();
		$article_ids = $this->get_cached_article_ids_for_author( $author_id );
		if ( empty( $article_ids ) ) {
			return array();
		}

		$args = array(
			'posts_per_page' => $amount,
			'post__in'       => $article_ids,
		);
		return post::get_many( $args );
	}

	/**
	 * Gets a list of article ids for the author, with caching.
	 *
	 * @param integer $author_id
	 * @return array<int>
	 */
	private function get_cached_article_ids_for_author( int $author_id ): array {
		$cache_key   = sprintf( 'articles_by_author_%d', $author_id );
		$cache_value = wp_cache_get( $cache_key, 'widgets' );
		if ( is_array( $cache_value ) ) {
			return $cache_value;
		}

		$article_ids = $this->get_article_ids_for_author( $author_id );
		wp_cache_set( $cache_key, $article_ids, 'widgets', 10 * MINUTE_IN_SECONDS );
		return $article_ids;
	}

	/**
	 * Gets a list of possible article ids for the author.
	 * This is a custom db query that doesn't do an orderby, for performance reasons.
	 * Instead, ordering (by post_id DESC) and limiting (100) is done in php.
	 * This should return a usable result for most cases.
	 *
	 * @param integer $author_id
	 * @return array<int>
	 */
	private function get_article_ids_for_author( int $author_id ): array {
		global $wpdb;
		$post_ids = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT post_id FROM $wpdb->postmeta WHERE meta_key = %s AND meta_value LIKE %s",
				PostTypesPost::META_AUTHORS,
				'%"' . serialize( (string) $author_id ) . '"%', // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
			)
		);

		$post_ids = array_map( 'absint', $post_ids );
		rsort( $post_ids, SORT_NUMERIC );
		$post_ids = array_slice( $post_ids, 0, 100 );
		return $post_ids;
	}
}
