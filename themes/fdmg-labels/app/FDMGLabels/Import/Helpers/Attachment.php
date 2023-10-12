<?php
namespace FDMGLabels\Import\Helpers;

class Attachment {

	protected const IMPORTED_ATTACHMENTS_DIR = 'imported';

	public function get_title_from_file_name( string $filename ): string {
		return basename( $filename );
	}

	public function get_wp_attached_file( int $identifier, string $filename ): string {
		$filename = str_replace( 'https://energeia-external-prod.imgix.net/', '', $filename );
		$filename = str_replace( 'https://energeia-external-acc.imgix.net/', '', $filename );
		$filename = str_replace( 'https://energeia-external-development.imgix.net', '', $filename );
		$filename = str_replace( 'https://pensioenpro-external-prod.imgix.net/', '', $filename );
		$filename = str_replace( 'https://pensioenpro-external-acc.imgix.net/', '', $filename );
		$filename = str_replace( 'https://pensioenpro-external-development.imgix.net', '', $filename );
		return self::IMPORTED_ATTACHMENTS_DIR . '/' . substr( (string) $identifier, -3 ) . '/' . ltrim( $filename, '/\\' );
	}

	/**
	 * Generates WP attachment metadata from an FD Media Group picture.
	 *
	 * @param integer $identifier
	 * @param string $filename
	 * @return string
	 */
	public function get_attachment_metadata( int $identifier, string $filename ): string {
		$mime         = $this->get_mime_type( $identifier, $filename );
		$wp_file_path = $this->get_file_dir( $identifier, $filename );
		if ( str_starts_with( $mime, 'image/' ) ) {
			$imagesize = wp_getimagesize( $wp_file_path ) ?: array();
			return maybe_serialize(
				array(
					'width'    => $imagesize[0] ?? 0,
					'height'   => $imagesize[1] ?? 0,
					'file'     => _wp_relative_upload_path( $wp_file_path ),
					'filesize' => wp_filesize( $wp_file_path ),
					'sizes'    => array(),
				)
			);
		}

		return '';
	}

	public function get_file_dir( int $identifier, string $filename ): string {
		return wp_upload_dir()['basedir'] . '/' . $this->get_wp_attached_file( $identifier, $filename );

	}

	public function get_mime_type( int $identifier, string $filename ): string {
		$wp_file_path = $this->get_file_dir( $identifier, $filename );
		return mime_content_type( $wp_file_path ) ?: '';
	}

	public function get_parent( int $article_id, int $author_id ): int {
		$finder     = new ImportedContentFinder();
		$article_id = $finder->get_post_id_from_old_id( 'article_' . $article_id );
		if ( $article_id ) {
			return $article_id;
		}

		$author_id = $finder->get_post_id_from_old_id( 'author_' . $author_id );
		if ( $author_id ) {
			return $author_id;
		}

		return 0;
	}
}
