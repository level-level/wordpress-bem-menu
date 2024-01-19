<?php

namespace FDMGLabels\Import\CLI;

use Clarkson_Core\WordPress_Object\post as WordPress_ObjectPost;
use FDMGLabels\Import\Helpers\ImportedContentFinder;
use WP_CLI;

class ArticleRedirects {

	protected const BATCH_SIZE = 10000;

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
		$redirects_csv = $args[0] ?? '';
		if ( empty( $redirects_csv ) || ! str_ends_with( $redirects_csv, '.csv' ) ) {
			WP_CLI::error( sprintf( 'Redirects CSV "%s" is an invalid file name', $redirects_csv ) );
		}

		$redirects_file = fopen( $redirects_csv, 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! $redirects_file ) {
			WP_CLI::error( sprintf( 'Could not open redirects CSV "%s"', $redirects_csv ) );
		}

		// Process articles in batches
		$batch_number = 1;
		while ( $posts_batch = $this->get_posts_batch( $batch_number ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			WP_CLI::log( sprintf( 'Processing batch number %d', $batch_number ) );
			foreach ( $posts_batch as $post ) {
				$this->process_post( $post, $redirects_file );
			}
			++$batch_number;
		}

		fclose( $redirects_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		WP_CLI::success( 'All articles have been processed' );
	}

	/**
	 * Gets a single batch of posts
	 *
	 * @param integer $batch_number
	 * @return WordPress_ObjectPost[]
	 */
	protected function get_posts_batch( int $batch_number ): array {
		WP_CLI::log( sprintf( 'Getting batch number %d from database', $batch_number ) );

		/** @var WordPress_ObjectPost[] */
		return WordPress_ObjectPost::get_many(
			array(
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'post_status'    => 'any',
				'posts_per_page' => self::BATCH_SIZE, // phpcs:ignore WordPress.WP.PostsPerPage.posts_per_page_posts_per_page
				'paged'          => $batch_number,
				'meta_query'     => array(
					array(
						'key'     => 'fdmg_article_publication_url',
						'compare' => 'EXISTS',
					),
				),
			)
		);
	}

	/**
	 * Write single article redirect to redirects CSV file.
	 *
	 * @param resource $redirects_file
	 * @return void
	 */
	protected function process_post( WordPress_ObjectPost $post, $redirects_file ): void {
		WP_CLI::log( sprintf( 'Processing article ID "%d"', $post->get_id() ) );

		$old_url = $post->get_meta( 'fdmg_article_publication_url', true );
		if ( ! $old_url ) {
			WP_CLI::log( sprintf( 'Old publication url is empty for article ID "%d"', $post->get_id() ) );
			return;
		}

		$post_permalink = $post->get_permalink();
		if ( ! $post_permalink ) {
			WP_CLI::warning( sprintf( 'Could not find permalink for article ID "%d"', $post->get_id() ) );
			return;
		}

		$redirect_data = array(
			'source_url' => $old_url,
			'target_url' => str_replace( home_url(), '', $post_permalink ),
		);
		if ( $redirect_data['source_url'] === untrailingslashit( $redirect_data['target_url'] ) ) {
			WP_CLI::log( sprintf( 'Source and target URL are the same for article ID "%d"', $post->get_id() ) );
			return;
		}

		$written = fputcsv( $redirects_file, $redirect_data );
		if ( ! $written ) {
			WP_CLI::warning( sprintf( 'Could not write redirect for article ID "%d"', $post->get_id() ) );
			return;
		}

		WP_CLI::log( sprintf( 'Processed article ID "%d"', $post->get_id() ) );
	}
}
