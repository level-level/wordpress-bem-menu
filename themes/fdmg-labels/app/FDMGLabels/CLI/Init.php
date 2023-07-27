<?php

namespace FDMGLabels\CLI;

class Init {
	public function register_commands(): void {
		if ( class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'fdmg-labels selligent', Selligent::class );
			\WP_CLI::add_command( 'fdmg-labels acf-json-to-acf-extended', AcfJsonToAcfExtended::class );
			\WP_CLI::add_command( 'fdmg-labels migrate-gf-selligent-forms', MigrateGFSelligentForms::class );
		}
	}
}
