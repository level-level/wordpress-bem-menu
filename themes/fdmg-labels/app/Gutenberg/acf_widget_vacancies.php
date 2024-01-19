<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\ACFWidgets\Vacancies;
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
		$selected_posts  = ( $this->content_attributes[ Vacancies::WIDGET_META_SELECTED_POSTS ] ?? null ) ?: array();
		$selected_posts  = $this->get_public_posts_only( $selected_posts );
		$is_fill_enabled = (bool) ( $this->content_attributes[ Vacancies::WIDGET_META_IS_FILL_ENABLED ] ?? true );

		if ( $is_fill_enabled === false ) {
			return $selected_posts;
		}

		$objects = array();
		$amount  = ( $this->content_attributes[ Vacancies::WIDGET_META_AMOUNT ] ?? null ) ?: 3;

		$query_args = $this->get_query_args( array(), self::POST_TYPES, $selected_posts, (int) $amount, self::TAX_RELATION );
		$wp_query   = new WP_Query( $query_args );

		if ( ! empty( $wp_query->posts ) ) {
			$posts = array_filter(
				$wp_query->posts, function ( $post ): bool {
					return $post instanceof WP_Post;
				}
			);

			$objects = Objects::get_instance()->get_objects( $posts );
		}

		$posts = array_merge( $selected_posts, $objects );

		return $posts;
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
}
