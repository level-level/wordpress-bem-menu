<?php

namespace FDMGLabels\Import\CLI;

use FDMGLabels\Import\Helpers\ImportedContentFinder;
use WP_CLI;

class GenerateCommentsCSV {

	protected ImportedContentFinder $imported_content_finder;

	public function __construct() {
		$this->imported_content_finder = new ImportedContentFinder();
	}

	/**
	 * Generate a CSV file that can be used to import comments.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels import generate-comments-csv run /path/to/fdmg-comments.csv /path/to/generated-comments.csv
	 */
	public function run( array $args, array $assoc_args ): void {
		$fdmg_comments_csv = $args[0] ?? '';
		if ( empty( $fdmg_comments_csv ) || ! file_exists( $fdmg_comments_csv ) ) {
			WP_CLI::error( sprintf( 'FDMG comments CSV "%s" does not exist', $fdmg_comments_csv ) );
		}

		$generated_comments_csv = $args[1] ?? '';
		if ( empty( $generated_comments_csv ) || file_exists( $generated_comments_csv ) ) {
			WP_CLI::error( sprintf( 'Generated comments CSV "%s" is an invalid file name', $generated_comments_csv ) );
		}

		$this->process_comments( $fdmg_comments_csv, $generated_comments_csv );
		WP_CLI::success( 'All comments have been processed' );
	}

	/**
	 * Loop all comments and create a CSV line for each comment.
	 */
	protected function process_comments( string $fdmg_comments_csv, string $generated_comments_csv ): void {
		$fdmg_comments_file = fopen( $fdmg_comments_csv, 'r' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $fdmg_comments_file ) ) {
			WP_CLI::error( sprintf( 'Could not open FDMG comments CSV "%s"', $fdmg_comments_csv ) );
		}

		$fdmg_comments_header = fgetcsv( $fdmg_comments_file );
		$fdmg_comments_header = ( $fdmg_comments_header && $fdmg_comments_header[0] === 'SEP=' ) ? fgetcsv( $fdmg_comments_file ) : $fdmg_comments_header;
		if ( ! $fdmg_comments_header ) {
			WP_CLI::error( sprintf( 'Could not read FDMG comments CSV header "%s"', $fdmg_comments_csv ) );
		}

		$generated_comments_file = fopen( $generated_comments_csv, 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $generated_comments_file ) ) {
			WP_CLI::error( sprintf( 'Could not create and open generated comments CSV "%s"', $generated_comments_csv ) );
		}

		$generated_comments_header_written = fputcsv(
			$generated_comments_file, array(
				'comment_id',
				'article_id',
				'customer_id',
				'customer_displayname',
				'date',
				'text',
				'approved',
			)
		);
		if ( ! $generated_comments_header_written ) {
			WP_CLI::error( sprintf( 'Could write generated comments CSV header "%s"', $generated_comments_header_written ) );
		}

		while ( $row = fgetcsv( $fdmg_comments_file ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$generated_comment = $this->get_generated_comment( array_combine( $fdmg_comments_header, $row ) );
			if ( empty( $generated_comment ) ) {
				continue;
			}

			$line_written = fputcsv( $generated_comments_file, $generated_comment );
			if ( ! $line_written ) {
				WP_CLI::warning( sprintf( 'Could not write generated comment "%s"', $generated_comment['comment_id'] ) );
				continue;
			}
		}

		fclose( $fdmg_comments_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $generated_comments_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}

	/**
	 * Generate a CSV line that's prepared for adding comments with WP All Import.
	 *
	 * @param array<string,string> $row
	 * @return array<string,string>
	 */
	protected function get_generated_comment( array $row ): array {
		$comment_id = sprintf( '%s - %s - %s', $row['articleId'], $row['customerId'], $row['submitDate'] );

		switch ( $row['state'] ) {
			case 'INVISIBLE':
				$approved = 'trash'; // Trash
				break;
			case 'MODERATED':
				$approved = '0'; // Pending
				break;
			default:
				$approved = '1'; // Approved
				break;
		}
		WP_CLI::log( sprintf( 'Processing comment ID "%s"', $comment_id ) );
		return array(
			'comment_id'           => $comment_id,
			'article_id'           => $row['articleId'],
			'customer_id'          => $row['customerId'],
			'customer_displayname' => $row['customerName'] ?: 'Anoniem',
			'date'                 => $row['submitDate'],
			'text'                 => $row['text'],
			'approved'             => $approved,
		);
	}
}
