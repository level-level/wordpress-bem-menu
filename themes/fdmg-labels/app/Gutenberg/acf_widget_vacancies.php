<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\PostTypes\Vacancy;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use WP_Post;
use WP_Query;

class acf_widget_vacancies extends base_block {

	public const POST_TYPES   = array( Vacancy::POST_TYPE_NAME );
	public const TAX_RELATION = 'OR';

	/**
	* Get latest posts based on user defined options
	*/
	public function get_posts(): array {
		$selected_posts   = ( $this->content_attributes['selected_posts'] ?? null ) ?: array();
		$is_fill_disabled = ( $this->content_attributes['is_fill_disabled'] ?? null ) ?: false;

		// Only show published posts even when future/drafts posts are selected
		$selected_posts = array_filter(
			$selected_posts,
			function( Clarkson_Object $object ) {
				return $object->get_status() === 'publish';
			}
		);

		if ( $is_fill_disabled ) {
			return $selected_posts;
		}

		$objects = array();
		$amount  = ( $this->content_attributes['amount'] ?? null ) ?: 3;

		$query_args = $this->get_query_args( array(), self::POST_TYPES, $selected_posts, (int) $amount, self::TAX_RELATION );
		$wp_query   = new WP_Query( $query_args );

		if ( ! empty( $wp_query->posts ) ) {
			$posts = array_filter(
				$wp_query->posts, function( $post ): bool {
					return $post instanceof WP_Post;
				}
			);

			$objects = Objects::get_instance()->get_objects( $posts );
		}

		return array_merge( $selected_posts, $objects );
	}
}
