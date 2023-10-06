<?php
namespace FDMGLabels\Import\WPAllImport;

class Init {

	public function register_hooks(): void {
		add_filter( 'import_functions_file_path', array( $this, 'change_functions_file_path' ) );
	}

	public function change_functions_file_path( string $file_path ): string {
		$file_path = __DIR__ . '/functions.php';
		return $file_path;
	}
}
