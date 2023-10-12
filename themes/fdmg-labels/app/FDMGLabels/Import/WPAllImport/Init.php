<?php
namespace FDMGLabels\Import\WPAllImport;

use FDMGLabels\Import\Helpers\Attachment;
use PMXI_Import_Record;

class Init {

	public function register_hooks(): void {
		add_filter( 'import_functions_file_path', array( $this, 'change_functions_file_path' ) );
		add_filter( 'pmxi_custom_types', array( $this, 'add_extra_post_types' ), 10, 2 );
		add_filter( 'pmxi_article_data', array( $this, 'set_post_mime_type_for_attachment' ), 10, 4 );
		add_filter( 'pmxi_article_data', array( $this, 'set_post_parent_for_attachment' ), 10, 4 );
	}

	public function change_functions_file_path( string $file_path ): string {
		$file_path = __DIR__ . '/functions.php';
		return $file_path;
	}

	/**
	 * Add extra post types that can be imported by WP All Import.
	 *
	 * @param array<string,WP_Post_Type> $post_types
	 * @return array<string,WP_Post_Type>
	 */
	public function add_extra_post_types( array $post_types, string $type ): array {
		$extra_post_types = array(
			'attachment',
		);
		foreach ( $extra_post_types as $extra_post_type ) {
			$object = get_post_type_object( $extra_post_type );
			if ( $object ) {
				$post_types[ $object->name ] = $object;
			}
		}
		return $post_types;
	}

	/**
	 * @param array<string,mixed> $article_data
	 * @param PMXI_Import_Record $import
	 * @param WP_Post|false $post_to_update
	 * @param array<string,mixed> $current_xml_node
	 * @return array<string,mixed>
	 */
	public function set_post_mime_type_for_attachment( array $article_data, PMXI_Import_Record $import, $post_to_update, array $current_xml_node ): array {
		if ( $article_data['post_type'] !== 'attachment' ) {
			return $article_data;
		}

		$article_data['post_mime_type'] = ( new Attachment() )->get_mime_type( $current_xml_node['id'], $current_xml_node['path'] );
		return $article_data;
	}

	/**
	 * Because attachments are not hierarchical, WP All Import doesn't
	 * allow assigning a parent post. However, WordPress makes any post it's
	 * uploaded to first it's parent post.
	 * This function force sets the post parent to be that post.
	 *
	 * @param array<string,mixed> $article_data
	 * @param PMXI_Import_Record $import
	 * @param WP_Post|false $post_to_update
	 * @param array<string,mixed> $current_xml_node
	 * @return array<string,mixed>
	 */
	public function set_post_parent_for_attachment( array $article_data, PMXI_Import_Record $import, $post_to_update, array $current_xml_node ): array {
		if ( $article_data['post_type'] !== 'attachment' ) {
			return $article_data;
		}

		$article_data['post_parent'] = ( new Attachment() )->get_parent( (int) $current_xml_node['article_id'], (int) $current_xml_node['author_id'] );
		return $article_data;
	}
}
