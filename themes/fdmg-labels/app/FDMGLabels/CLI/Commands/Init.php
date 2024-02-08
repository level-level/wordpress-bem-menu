<?php

namespace FDMGLabels\CLI\Commands;

class Init {
	public function register_commands(): void {
		if ( class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'fdmg-labels commands send-newsletter', SendNewsletter::class );
			\WP_CLI::add_command( 'fdmg-labels commands optin-email-for-newsletter', OptinEmailForNewsletter::class );
		}
	}
}
