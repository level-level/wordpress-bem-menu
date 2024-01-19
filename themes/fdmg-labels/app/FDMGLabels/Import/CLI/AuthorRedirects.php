<?php

namespace FDMGLabels\Import\CLI;

use Clarkson\Theme\PostTypes\Team;
use FDMGLabels\Import\Helpers\ImportedContentFinder;
use WP_CLI;

class AuthorRedirects {

	protected ImportedContentFinder $imported_content_finder;

	public function __construct() {
		$this->imported_content_finder = new ImportedContentFinder();
	}

	/**
	 * Generate a redirects CSV based on provided authors CSV file.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels import author-redirects run /path/to/authors.csv /path/to/redirects.csv
	 */
	public function run( array $args, array $assoc_args ): void {
		$authors_csv = $args[0] ?? '';
		if ( empty( $authors_csv ) || ! file_exists( $authors_csv ) ) {
			WP_CLI::error( sprintf( 'Authors CSV "%s" does not exist', $authors_csv ) );
		}

		$redirects_csv = $args[1] ?? '';
		if ( empty( $redirects_csv ) || ! str_ends_with( $redirects_csv, '.csv' ) ) {
			WP_CLI::error( sprintf( 'Redirects CSV "%s" is an invalid file name', $authors_csv ) );
		}

		$authors_file = fopen( $authors_csv, 'r' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $authors_file ) ) {
			WP_CLI::error( sprintf( 'Could not open authors CSV "%s"', $authors_csv ) );
		}

		$redirects_file = fopen( $redirects_csv, 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $redirects_file ) ) {
			WP_CLI::error( sprintf( 'Could not open redirects CSV "%s"', $redirects_csv ) );
		}

		$authors_header = fgetcsv( $authors_file );
		if ( ! $authors_header ) {
			WP_CLI::error( sprintf( 'Could not read authors CSV header "%s"', $authors_csv ) );
		}

		while ( $row = fgetcsv( $authors_file ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$this->process_author( array_combine( $authors_header, $row ), $redirects_file );
		}

		fclose( $authors_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $redirects_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		WP_CLI::success( 'All authors have been processed' );
	}

	/**
	 * Write single author redirect to redirects CSV file.
	 *
	 * @param array<string,string> $row
	 * @param resource $redirects_file
	 * @return void
	 */
	protected function process_author( array $row, $redirects_file ): void {
		$author_id = $row['author_id'];

		WP_CLI::log( sprintf( 'Processing author ID "%d"', $author_id ) );
		if ( empty( $row['directory'] ) ) {
			WP_CLI::log( sprintf( 'Author ID "%d" is not public, skipping...', $author_id ) );
			return;
		}

		$post = $this->imported_content_finder->get_post_id_from_old_id( 'author_' . $author_id, Team::POST_TYPE_NAME );
		if ( ! $post ) {
			WP_CLI::warning( sprintf( 'Could not find WordPress post for author ID "%d"', $author_id ) );
			return;
		}

		$post_permalink = get_permalink( $post );
		if ( ! $post_permalink ) {
			WP_CLI::warning( sprintf( 'Could not find permalink for author ID "%d"', $author_id ) );
			return;
		}

		$redirect_data = array(
			'source_url' => '/auteur/' . $row['directory'],
			'target_url' => str_replace( home_url(), '', $post_permalink ),
		);
		if ( $redirect_data['source_url'] === untrailingslashit( $redirect_data['target_url'] ) ) {
			WP_CLI::log( sprintf( 'Source and target URL are the same for author ID "%d"', $author_id ) );
			return;
		}

		$written = fputcsv( $redirects_file, $redirect_data );
		if ( ! $written ) {
			WP_CLI::warning( sprintf( 'Could not write redirect for author ID "%d"', $author_id ) );
			return;
		}

		WP_CLI::log( sprintf( 'Processed author ID "%d"', $author_id ) );
	}
}
