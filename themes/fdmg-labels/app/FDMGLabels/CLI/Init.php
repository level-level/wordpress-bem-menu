<?php

namespace FDMGLabels\CLI;

use FDMGLabels\CLI\Commands\Init as CommandsInit;

class Init {
	public function register_commands(): void {
		if ( class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'fdmg-labels selligent', Selligent::class );
			\WP_CLI::add_command( 'fdmg-labels acf-json-to-acf-extended', AcfJsonToAcfExtended::class );
			\WP_CLI::add_command( 'fdmg-labels migrate-gf-selligent-forms', MigrateGFSelligentForms::class );
			\WP_CLI::add_command( 'fdmg-labels migrate-selligent-newsletters', MigrateSelligentNewsletters::class );
			\WP_CLI::add_command( 'fdmg-labels fdmg-content-api', FDMGContentAPI::class );

			( new CommandsInit() )->register_commands();
		}
	}
}
