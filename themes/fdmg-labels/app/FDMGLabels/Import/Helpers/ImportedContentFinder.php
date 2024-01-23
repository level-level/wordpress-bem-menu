<?php
namespace FDMGLabels\Import\Helpers;

use Clarkson\Theme\Taxonomies\Section;
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

	public function get_attachment_id_from_old_path( string $old_path ): int {
		/** @var int[] */
		$posts = get_posts(
			array(
				'fields'         => 'ids',
				'post_type'      => 'attachment',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => AbstractAttachments::META_OLD_PATH,
						'value' => $old_path,
					),
				),
			)
		);
		return (int) array_shift( $posts );
	}

	public function get_download_id_from_old_path( string $old_path ): int {
		/** @var int[] */
		$posts = get_posts(
			array(
				'fields'         => 'ids',
				'post_type'      => 'dlm_download',
				'post_status'    => 'any',
				'posts_per_page' => 1,
				'meta_query'     => array(
					array(
						'key'   => AbstractAttachments::META_OLD_PATH,
						'value' => $old_path,
					),
				),
			)
		);
		return (int) array_shift( $posts );
	}

	public function get_author_ids_by_old_ids( ?string $ids ): string {
		if ( empty( $ids ) ) {
			return '';
		}
		$ids    = explode( ',', $ids );
		$return = array();
		foreach ( $ids as $id ) {
			$return[] = $this->get_post_id_from_old_id( 'author_' . $id );
		}
		return implode( ',', $return );
	}


	public function get_categories_from_old_ids( ?string $ids ): string {
		if ( empty( $ids ) ) {
			return '';
		}

		$ids = explode( ',', $ids );
		foreach ( $ids as &$id ) {
			$id = 'tag_' . $id;
		}
		$terms = get_terms(
			array(
				'taxonomy'   => 'category',
				'hide_empty' => false,
				'fields'     => 'names',
				'meta_query' => array(
					array(
						'key'     => Init::META_OLD_ID,
						'compare' => 'IN',
						'value'   => $ids,
					),
				),
			)
		);

		if ( is_array( $terms ) ) {
			return implode( ',', $terms );
		}

		return '';
	}

	/**
	 * @param string|null $id
	 * @return string
	 */
	public function get_section_from_old_id( ?string $id ): string {
		if ( empty( $id ) ) {
			return '';
		}

		$id = 'section_' . $id;

		$terms = get_terms(
			array(
				'taxonomy'   => Section::TAXONOMY_NAME,
				'hide_empty' => false,
				'number'     => 1,
				'fields'     => 'names',
				'meta_query' => array(
					array(
						'key'   => Init::META_OLD_ID,
						'value' => $id,
					),
				),
			)
		);

		if ( is_array( $terms ) && ! empty( $terms ) ) {
			return array_pop( $terms );
		}

		return '';
	}
}
