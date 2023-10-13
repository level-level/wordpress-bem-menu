<?php
namespace FDMGLabels\Import\WPAllImport;

use DLM_Media_Library;
use FDMGLabels\Import\Helpers\Attachment;
use FDMGLabels\Import\Helpers\ContentParser;
use FDMGLabels\Import\Init as ImportInit;
use PMXI_Import_Record;
use SimpleXMLElement;
use WP_Post;
use WP_Post_Type;

class Init {

	public function register_hooks(): void {
		add_filter( 'import_functions_file_path', array( $this, 'change_functions_file_path' ) );
		add_filter( 'pmxi_custom_types', array( $this, 'add_extra_post_types' ), 10, 2 );
		add_filter( 'pmxi_article_data', array( $this, 'set_post_data_for_attachment' ), 10, 4 );
		add_action( 'pmxi_saved_post', array( $this, 'convert_pdf_to_download' ), 10, 2 );
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
		 * Because attachments are not hierarchial, WP All Import doesn't
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

	/**
	 * PDF files should be imported as Downloads, not as media library items.
	 * Download Monitor has builtin functionality to convert a media library item to a download.
	 * This function runs directly after importing a media library item, and converts it to a download.
	 */
	public function convert_pdf_to_download( int $id, SimpleXMLElement $xml ): void {
		// If it's not an attachment or is already protected, skip it.
		if ( 'attachment' !== get_post_type( $id ) || ( '1' === get_post_meta( $id, 'dlm_protected_file', true ) ) ) {
			return;
		}

		// If it's not a binary file, skip
		$binary_type = (string) $xml->binary_type;
		if ( $binary_type !== 'PDF' ) {
			return;
		}

		// Create the file object.
		$file = array(
			'attachment_id' => $id,
			'user_id'       => get_current_user_id(),
			'title'         => get_the_title( $id ),
		);

		// Move the file and create the Download.
		download_monitor()->service( 'file_manager' )->move_file_to_dlm_uploads( $id );
		/**
		 * DLM docbloc is wrong. They say it's an object, but it's a DLM_Media_Library instance.
		 * @var DLM_Media_Library
		 */
		$dlm_media_library = DLM_Media_Library::get_instance();
		$dlm_media_library->create_download( $file );

		// Update meta of the download
		$assigned_download_id = get_post_meta( $id, 'dlm_download', true ) ?: '';
		$assigned_download_id = json_decode( $assigned_download_id, true ) ?: array();
		$assigned_download_id = $assigned_download_id['download_id'] ?? 0;

		// Duplicate meta from attachment to download.
		$old_id   = get_post_meta( $id, ImportInit::META_OLD_ID, true );
		$old_path = get_post_meta( $id, Attachment::META_OLD_PATH, true );
		update_post_meta( $assigned_download_id, ImportInit::META_OLD_ID, $old_id );
		update_post_meta( $assigned_download_id, Attachment::META_OLD_PATH, $old_path );
	}
}
