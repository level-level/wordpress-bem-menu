<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\FeaturedItem;
use Clarkson\Theme\ContentBlockRegister;
use Clarkson\Theme\PostTypes\Post;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use WP_Post;
use WP_Query;

class acf_widget_featured_item extends base_block {

	public const POST_TYPES   = array( Post::POST_TYPE_NAME );
	public const TAX_RELATION = 'OR';

	/**
	* Get latest posts based on user defined options
	* @return Clarkson_Object[]
	*/
	public function get_posts(): array {
		$register        = ContentBlockRegister::get_instance();
		$selected_posts  = ( $this->content_attributes[ FeaturedItem::BLOCK_META_SELECTED_POSTS ] ?? null ) ?: array();
		$selected_posts  = $this->get_public_posts_only( $selected_posts );
		$is_fill_enabled = (bool) ( $this->content_attributes[ FeaturedItem::BLOCK_META_IS_FILL_ENABLED ] ?? true );

		$this->register_ids( $register, $selected_posts );

		if ( $is_fill_enabled === false ) {
			return $selected_posts;
		}

		$objects = array();
		$terms   = $this->get_selected_terms();
		$amount  = 1;

		$query_args = $this->get_query_args( $terms, self::POST_TYPES, $selected_posts, $amount, self::TAX_RELATION );

		if ( ! empty( $query_args ) ) {
			$query_args = $register->maybe_add_id_exclude_to_query_args( $query_args );
			$wp_query   = new WP_Query( $query_args );

			if ( ! empty( $wp_query->posts ) ) {
				$posts = array_filter(
					$wp_query->posts, function( $post ): bool {
						return $post instanceof WP_Post;
					}
				);

				$objects = Objects::get_instance()->get_objects( $posts );
			}
		}

		$posts = array_merge( $selected_posts, $objects );

		$this->register_ids( $register, $posts );

		return $posts;
	}

	/**
	 * Map posts IDs and add to content block register
	 *
	 * @param ContentBlockRegister $register
	 * @param array<Clarkson_Object> $posts
	 * @return void
	 */
	public function register_ids( ContentBlockRegister $register, array $posts ): void {
		$ids = array_map(
			function( $post ): int {
				return $post->get_id();
			},
			$posts
		);

		$register->add_ids( $ids );
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
			function( Clarkson_Object $object ) {
				return $object->get_status() === 'publish';
			}
		);
	}

	/**
	 * Returns array of selected terms
	 * @return array<Clarkson_Object>
	 */
	public function get_selected_terms(): array {
		$categories = $this->content_attributes[ FeaturedItem::BLOCK_META_TERMS ] ?? null;
		$categories = $categories ?: array();
		$genres     = $this->content_attributes[ FeaturedItem::BLOCK_META_GENRES ] ?? null;
		$genres     = $genres ?: array();
		$sections   = $this->content_attributes[ FeaturedItem::BLOCK_META_SECTIONS ] ?? null;
		$sections   = $sections ?: array();

		return array_merge( $categories, $genres, $sections );
	}
}
