<?php

namespace FDMGLabels\Import\CLI;

class Init {
	public function register_commands(): void {
		if ( class_exists( '\\WP_CLI' ) ) {
			\WP_CLI::add_command( 'fdmg-labels import article-redirects', ArticleRedirects::class );
			\WP_CLI::add_command( 'fdmg-labels import author-redirects', AuthorRedirects::class );
			\WP_CLI::add_command( 'fdmg-labels import generate-comments-csv', GenerateCommentsCSV::class );
		}
	}
}
