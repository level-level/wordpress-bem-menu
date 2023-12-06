<?php
namespace FDMGLabels\Import\Helpers;

class BinaryFiles extends AbstractAttachments {

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
}
