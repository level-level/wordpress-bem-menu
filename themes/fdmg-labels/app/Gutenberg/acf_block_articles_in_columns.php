<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\PostTypes\Post;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use FDMGLabels\Articles\ArticleBlockRegister;
use WP_Post;
use WP_Query;

class acf_block_articles_in_columns extends base_block {

	public const POST_TYPES   = array( Post::POST_TYPE_NAME );
	public const TAX_RELATION = 'OR';

	/**
	* Get latest posts based on user defined options
	*/
	public function get_posts(): array {
		$register         = ArticleBlockRegister::get_instance();
		$selected_posts   = ( $this->content_attributes['selected_posts'] ?? null ) ?: array();
		$is_fill_disabled = ( $this->content_attributes['is_fill_disabled'] ?? null ) ?: false;

		// Only show published posts even when future/drafts posts are selected
		$selected_posts = array_filter(
			$selected_posts,
			function( Clarkson_Object $object ) {
				return $object->get_status() === 'publish';
			}
		);

		$ids = array_map(
			function( $post ): int {
				return $post->get_id();
			},
			$selected_posts
		);

		$register->add_ids( $ids );

		if ( $is_fill_disabled ) {
			return $selected_posts;
		}

		$objects = array();
		$terms   = ( $this->content_attributes['terms'] ?? null ) ?: array();
		$amount  = ( $this->content_attributes['amount'] ?? null ) ?: 2;

		$query_args = $this->get_query_args( $terms, self::POST_TYPES, $selected_posts, (int) $amount, self::TAX_RELATION );

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

		$ids = array_map(
			function( $post ) {
				return $post->get_id();
			},
			$posts
		);

		$register->add_ids( $ids );

		return $posts;
	}
}
