<?php
namespace FDMGLabels\Import;

use FDMGLabels\Import\CLI\Init as CLIInit;
use FDMGLabels\Import\WPAllImport\Init as WPAllImportInit;

class Init {

	public const META_OLD_ID = 'fdmg_import_old_id';

	public function register_hooks(): void {
		( new WPAllImportInit() )->register_hooks();
		( new CLIInit() )->register_commands();
	}
}
