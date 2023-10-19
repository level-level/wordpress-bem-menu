<?php
namespace FDMGLabels\Import\Helpers;

class BinaryFiles extends Attachments {

	protected const IMPORTED_ATTACHMENTS_DIR = 'imported-binary-files';

	public function get_wp_attached_file( int $identifier, string $filename ): string {
		$filename = str_replace( 'https://energeia-binary-external-prod.imgix.net/', '', $filename );
		$filename = str_replace( 'https://energeia-binary-external-acc.imgix.net/', '', $filename );
		$filename = str_replace( 'https://energeia-binary-external-development.imgix.net', '', $filename );
		$filename = str_replace( 'https://pensioenpro-binary-external-prod.imgix.net/', '', $filename );
		$filename = str_replace( 'https://pensioenpro-binary-external-acc.imgix.net/', '', $filename );
		$filename = str_replace( 'https://pensioenpro-binary-external-development.imgix.net', '', $filename );
		return self::IMPORTED_ATTACHMENTS_DIR . '/' . substr( (string) $identifier, -3 ) . '/' . ltrim( $filename, '/\\' );
	}

	public function get_file_dir( int $identifier, string $filename ): string {
		return wp_upload_dir()['basedir'] . '/' . $this->get_wp_attached_file( $identifier, $filename );

	}

	public function get_mime_type( int $identifier, string $filename ): string {
		$wp_file_path = $this->get_file_dir( $identifier, $filename );
		return mime_content_type( $wp_file_path ) ?: '';
	}
}
