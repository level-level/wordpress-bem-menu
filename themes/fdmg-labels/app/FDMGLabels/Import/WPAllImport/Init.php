<?php
namespace FDMGLabels\Import\WPAllImport;

use FDMGLabels\Import\Helpers\Attachment;
use FDMGLabels\Import\Helpers\ContentParser;
use PMXI_Import_Record;
use WP_Post;
use WP_Post_Type;

class Init {

	public function register_hooks(): void {
		add_filter( 'import_functions_file_path', array( $this, 'change_functions_file_path' ) );
		add_filter( 'pmxi_custom_types', array( $this, 'add_extra_post_types' ), 10, 2 );
		add_filter( 'pmxi_article_data', array( $this, 'set_post_data_for_attachment' ), 10, 4 );
		add_action( 'pmxi_saved_post', array( $this, 'set_post_mime_type_for_attachment' ), 10, 2 );
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
	 * Because attachments are not default supported in WP All Import,
	 * we need to overwrite/set some post data.
	 *
	 * @param array<string,mixed> $article_data
	 * @param PMXI_Import_Record $import
	 * @param WP_Post|false $post_to_update
	 * @param array<string,mixed> $current_xml_node
	 * @return array<string,mixed>
	 */
	public function set_post_data_for_attachment( array $article_data, PMXI_Import_Record $import, $post_to_update, array $current_xml_node ): array {
		if ( $article_data['post_type'] !== 'attachment' ) {
			return $article_data;
		}

		$mime_type = ( new Attachment() )->get_mime_type( $current_xml_node['id'], $current_xml_node['path'] );

		/**
		 * Mime type of attachment is saved on the post, but WP All Import
		 * doesn't by default allow this.
		 */
		$article_data['post_mime_type'] = $mime_type;

		/**
		 * Because attachments are not hierarchical, WP All Import doesn't
		 * allow assigning a parent post. However, WordPress makes any post it's
		 * uploaded to first it's parent post.
		 * This force sets the post parent to be that post.
		 */
		$article_data['post_parent'] = ( new Attachment() )->get_parent( (int) ( $current_xml_node['article_id'] ?? 0 ), (int) ( $current_xml_node['author_id'] ?? 0 ) );

		if ( str_starts_with( $mime_type, 'image/' ) ) {
			/**
			 * In the FDMG Labels theme, the image caption (excerpt) is used to show a description below a photo.
			 * We cannot set the excerpt via WP All Import, because attachments don't
			 * officially support excerpts. So instead, we set it ourselves.
			 */
			$excerpt_parts = array();
			if ( ! empty( $current_xml_node['caption'] ) && is_string( $current_xml_node['caption'] ) ) {
				$excerpt_parts[] = $current_xml_node['caption'];
			}
			if ( ! empty( $current_xml_node['photographer'] ) && is_string( $current_xml_node['photographer'] ) ) {
				$excerpt_parts[] = $current_xml_node['photographer'];
			}

			$article_data['post_excerpt'] = join( ' | ', $excerpt_parts );
		}

		return $article_data;
	}
}
