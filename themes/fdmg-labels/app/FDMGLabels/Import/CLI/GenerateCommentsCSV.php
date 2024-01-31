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
	 * This combines the by FDMG provided comments and customers CSV files.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels import generate-comments-csv run /path/to/fdmg-comments.csv /path/to/fdmg-customers.csv /path/to/generated-comments.csv
	 */
	public function run( array $args, array $assoc_args ): void {
		$fdmg_comments_csv = $args[0] ?? '';
		if ( empty( $fdmg_comments_csv ) || ! file_exists( $fdmg_comments_csv ) ) {
			WP_CLI::error( sprintf( 'FDMG comments CSV "%s" does not exist', $fdmg_comments_csv ) );
		}

		$fdmg_customers_csv = $args[1] ?? '';
		if ( empty( $fdmg_customers_csv ) || ! file_exists( $fdmg_customers_csv ) ) {
			WP_CLI::error( sprintf( 'FDMG customers CSV "%s" is an invalid file name', $fdmg_customers_csv ) );
		}

		$generated_comments_csv = $args[2] ?? '';
		if ( empty( $generated_comments_csv ) || file_exists( $generated_comments_csv ) ) {
			WP_CLI::error( sprintf( 'Generated comments CSV "%s" is an invalid file name', $generated_comments_csv ) );
		}

		$customers = $this->get_extracted_customers( $fdmg_customers_csv );
		$this->process_comments( $fdmg_comments_csv, $generated_comments_csv, $customers );
		WP_CLI::success( 'All comments have been processed' );
	}

	/**
	 * Extract the customers from the FDMG customers CSV file.
	 *
	 * @return array<int,string> Key-value array containing the customer_id => displayname
	 */
	protected function get_extracted_customers( string $fdmg_customers_csv ): array {
		$fdmg_customers_file = fopen( $fdmg_customers_csv, 'r' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $fdmg_customers_file ) ) {
			WP_CLI::error( sprintf( 'Could not open FDMG customers CSV "%s"', $fdmg_customers_file ) );
			exit;
		}

		$customers = array();
		while ( $row = fgetcsv( $fdmg_customers_file ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$customers[ (int) $row[0] ] = $row[1];
		}

		fclose( $fdmg_customers_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		return $customers;
	}

	/**
	 * Loop all comments and create a CSV line for each comment.
	 *
	 * @param array<int,string> $customers
	 */
	protected function process_comments( string $fdmg_comments_csv, string $generated_comments_csv, array $customers ): void {
		$fdmg_comments_file = fopen( $fdmg_comments_csv, 'r' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $fdmg_comments_file ) ) {
			WP_CLI::error( sprintf( 'Could not open FDMG comments CSV "%s"', $fdmg_comments_csv ) );
			exit;
		}

		$fdmg_comments_header = fgetcsv( $fdmg_comments_file );
		$fdmg_comments_header = ( $fdmg_comments_header && $fdmg_comments_header[0] === 'SEP=' ) ? fgetcsv( $fdmg_comments_file ) : $fdmg_comments_header;
		if ( ! $fdmg_comments_header ) {
			WP_CLI::error( sprintf( 'Could not read FDMG comments CSV header "%s"', $fdmg_comments_csv ) );
			exit;
		}

		$generated_comments_file = fopen( $generated_comments_csv, 'w' ); // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_operations_fopen
		if ( ! is_resource( $generated_comments_file ) ) {
			WP_CLI::error( sprintf( 'Could not create and open generated comments CSV "%s"', $generated_comments_csv ) );
			exit;
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
			exit;
		}

		while ( $row = fgetcsv( $fdmg_comments_file ) ) { // phpcs:ignore Generic.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			$generated_comment = $this->get_generated_comment( array_combine( $fdmg_comments_header, $row ), $customers );
			if ( empty( $generated_comment ) ) {
				continue;
			}

			$line_written = fputcsv( $generated_comments_file, $generated_comment );
			if ( ! $line_written ) {
				WP_CLI::warning( sprintf( 'Could not write generated comment "%s"', $generated_comment['comment_id'] ) );
				exit;
			}
		}

		fclose( $fdmg_comments_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
		fclose( $generated_comments_file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_fclose
	}

	/**
	 * Generate a CSV line that's prepared for adding comments with WP All Import.
	 *
	 * @param array<string,string> $row
	 * @param array<int,string> $customers
	 * @return array<string,string>
	 */
	protected function get_generated_comment( array $row, array $customers ): array {
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
			'customer_displayname' => $customers[ (int) $row['customerId'] ] ?? 'Anoniem',
			'date'                 => $row['submitDate'],
			'text'                 => $row['text'],
			'approved'             => $approved,
		);
	}
}
