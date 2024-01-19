<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFBlocks\RelatedArticles;
use Clarkson\Theme\PostTypes\Post;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use FDMGLabels\FeatureManager;
use WP_Post;
use WP_Query;

class acf_block_related_articles extends base_block {

	public const POST_TYPES   = array( Post::POST_TYPE_NAME );
	public const TAX_RELATION = 'OR';

	/**
	* Get latest posts based on user defined options
	*/
	public function get_posts(): array {
		$selected_posts = ( $this->content_attributes[ RelatedArticles::BLOCK_META_SELECTED_POSTS ] ?? null ) ?: array();
		$selected_posts = $this->get_public_posts_only( $selected_posts );

		// The default value is based on the feature flag.
		$is_fill_enabled = (bool) ( $this->content_attributes[ RelatedArticles::BLOCK_META_IS_FILL_ENABLED ] ?? ! ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_AUTOFILLING_RELATED_ARTICLES_BLOCK ) );

		if ( $is_fill_enabled === false ) {
			return $selected_posts;
		}

		$objects = array();
		$terms   = $this->get_selected_terms();
		$amount  = ( $this->content_attributes[ RelatedArticles::BLOCK_META_AMOUNT ] ?? null ) ?: 3;

		$query_args = $this->get_query_args( $terms, self::POST_TYPES, $selected_posts, (int) $amount, self::TAX_RELATION );
		$wp_query   = new WP_Query( $query_args );

		if ( ! empty( $wp_query->posts ) ) {
			$posts = array_filter(
				$wp_query->posts, function ( $post ): bool {
					return $post instanceof WP_Post;
				}
			);

			$objects = Objects::get_instance()->get_objects( $posts );
		}

		return array_merge( $selected_posts, $objects );
	}

	/**
	 * Only show published posts even when future/drafts posts are selected
	 *
	 * @param array<Clarkson_Object> $posts
	 * @return array<Clarkson_Object>
	 */
	public function get_public_posts_only( array $posts ): array {
		return array_filter(
			$posts,
			function ( Clarkson_Object $object ) {
				return $object->get_status() === 'publish';
			}
		);
	}

	/**
	 * Returns array of selected terms
	 * @return array<Clarkson_Object>
	 */
	public function get_selected_terms(): array {
		$categories = $this->content_attributes[ RelatedArticles::BLOCK_META_TERMS ] ?? null;
		$categories = $categories ?: array();
		$genres     = $this->content_attributes[ RelatedArticles::BLOCK_META_GENRES ] ?? null;
		$genres     = $genres ?: array();
		$sections   = $this->content_attributes[ RelatedArticles::BLOCK_META_SECTIONS ] ?? null;
		$sections   = $sections ?: array();

		return array_merge( $categories, $genres, $sections );
	}
}
