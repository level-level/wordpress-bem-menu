<?php
namespace FDMGLabels\Import\Helpers;

use FDMGLabels\Import\Init;

class ImportedContentFinder {

	public function get_post_id_from_old_id( string $old_id, string $post_type = 'any' ): int {
		/** @var int[] */
		$posts = get_posts(
			array(
				'fields'         => 'ids',
				'post_type'      => $post_type,
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => Init::META_OLD_ID,
						'value' => $old_id,
					),
				),
			)
		);
		return (int) array_shift( $posts );
	}

	public function get_user_id_from_old_id( string $old_id ): int {
		/** @var int[] */
		$users = get_users(
			array(
				'fields'     => 'ID',
				'number'     => 1,
				'meta_query' => array(
					array(
						'key'   => Init::META_OLD_ID,
						'value' => $old_id,
					),
				),
			)
		);
		return (int) array_shift( $users );
	}

	public function get_term_id_from_old_id( string $old_id, ?string $taxonomy = null ): int {
		/** @var int[] */
		$terms = get_terms(
			array(
				'taxonomy'   => $taxonomy ? $taxonomy : array(),
				'hide_empty' => false,
				'fields'     => 'ids',
				'number'     => 1,
				'meta_query' => array(
					array(
						'key'   => Init::META_OLD_ID,
						'value' => $old_id,
					),
				),
			)
		);
		return (int) array_shift( $terms );
	}

	public function get_comment_id_from_old_id( string $old_id, string $type = '' ): int {
		/** @var int[] */
		$terms = get_comments(
			array(
				'post_status' => 'any',
				'post_type'   => 'any',
				'type'        => '',
				'fields'      => 'ids',
				'number'      => 1,
				'meta_query'  => array(
					array(
						'key'   => Init::META_OLD_ID,
						'value' => $old_id,
					),
				),
			)
		);
		return (int) array_shift( $terms );
	}
}
