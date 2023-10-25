<?php

namespace FDMGLabels\Import\CLI;

use Clarkson\Theme\PostTypes\Post;
use FDMGLabels\Import\Helpers\ImportedContentFinder;
use WP_CLI;

class ArticleRedirects {

	protected ImportedContentFinder $imported_content_finder;

	public function __construct() {
		$this->imported_content_finder = new ImportedContentFinder();
	}

	/**
	 * Generate a redirects CSV based on provided articles CSV file.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels import article-redirects run /path/to/articles.csv /path/to/redirects.csv
	 */
	public function run( array $args, array $assoc_args ): void {
		$articles_csv = $args[0] ?? '';
		if ( empty( $articles_csv ) || ! file_exists( $articles_csv ) ) {
			WP_CLI::error( sprintf( 'Articles CSV "%s" does not exist', $articles_csv ) );
			exit;
		}

		$redirects_csv = $args[1] ?? '';
		if ( empty( $redirects_csv ) || ! str_ends_with( $redirects_csv, '.csv' ) ) {
			WP_CLI::error( sprintf( 'Redirects CSV "%s" is an invalid file name', $articles_csv ) );
			exit;
		}

		$articles_file = fopen( $articles_csv, 'r' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( ! $articles_file ) {
			WP_CLI::error( sprintf( 'Could not open articles CSV "%s"', $articles_csv ) );
			exit;
		}

		$redirects_file = fopen( $redirects_csv, 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_fopen
		if ( ! $redirects_file ) {
			WP_CLI::error( sprintf( 'Could not open redirects CSV "%s"', $redirects_csv ) );
			exit;
		}

		$articles_header = fgetcsv( $articles_file );
		if ( ! $articles_header ) {
			WP_CLI::error( sprintf( 'Could not read articles CSV header "%s"', $articles_csv ) );
			exit;
		}

		while ( $row = fgetcsv( $articles_file ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$this->process_article( array_combine( $articles_header, $row ), $redirects_file );
		}

		fclose( $articles_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		fclose( $redirects_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_read_fclose
		WP_CLI::success( 'All articles have been processed' );
	}

	/**
	 * Write single article redirect to redirects CSV file.
	 *
	 * @param array<string,string> $row
	 * @param resource $redirects_file
	 * @return void
	 */
	protected function process_article( array $row, $redirects_file ): void {
		WP_CLI::log( sprintf( 'Processing article ID "%d"', $row['id'] ) );

		$post = $this->imported_content_finder->get_post_id_from_old_id( 'article_' . $row['id'], Post::POST_TYPE_NAME );
		if ( ! $post ) {
			WP_CLI::warning( sprintf( 'Could not find WordPress post for article ID "%d"', $row['id'] ) );
			return;
		}

		$post_permalink = get_permalink( $post );
		if ( ! $post_permalink ) {
			WP_CLI::warning( sprintf( 'Could not find permalink for article ID "%d"', $row['id'] ) );
			return;
		}

		$redirect_data = array(
			'source_url' => $row['publication_url'],
			'target_url' => str_replace( home_url(), '', $post_permalink ),
		);
		if ( $redirect_data['source_url'] === $redirect_data['target_url'] ) {
			WP_CLI::log( sprintf( 'Source and target URL are the same for article ID "%d"', $row['id'] ) );
			return;
		}

		$written = fputcsv( $redirects_file, $redirect_data );
		if ( ! $written ) {
			WP_CLI::warning( sprintf( 'Could not write redirect for article ID "%d"', $row['id'] ) );
			return;
		}

		WP_CLI::log( sprintf( 'Processed article ID "%d"', $row['id'] ) );
	}
}
