<?php
namespace FDMGLabels\Import\Helpers;

class Attachments {

	public const META_OLD_PATH = 'fdmg_import_old_path';

	public function get_title( string $title, string $filename ): string {
		$title = ( new ContentParser() )->format_value( $title );
		if ( ! empty( $title ) ) {
			return $title;
		}

		// Fall back to file name
		return basename( $filename );
	}
}
