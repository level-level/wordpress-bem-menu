<?php
namespace FDMGLabels\Import\Helpers;

abstract class AbstractAttachments {

	public const META_OLD_PATH = 'fdmg_import_old_path';

	public function get_title( string $title, string $filename ): string {
		$title = ( new ContentParser() )->format_value( $title );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Fall back to file name
		return basename( $filename );
	}

	abstract public function get_wp_attached_file( int $identifier, string $filename ): string;

	public function get_file_dir( int $identifier, string $filename ): string {
		return wp_upload_dir()['basedir'] . '/' . $this->get_wp_attached_file( $identifier, $filename );
	}

	public function get_mime_type( int $identifier, string $filename ): string {
		$wp_file_path = $this->get_file_dir( $identifier, $filename );
		return mime_content_type( $wp_file_path ) ?: '';
	}
}
