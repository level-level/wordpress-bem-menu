<?php

namespace ImpactInvestor\CLI;

class Init {
	public function register_commands(): void {
		if ( class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'ii selligent', Selligent::class );
		}
	}
}
