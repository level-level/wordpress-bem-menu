<?php

namespace FDMGLabels\CLI\Commands;

use Clarkson\Theme\PostTypes\Newsletter;
use Exception;
use FDMGLabels\Commands\SendNewsletter as CommandsSendNewsletter;
use Monolog\Registry;
use WP_CLI;

use function WP_CLI\Utils\get_flag_value;

class SendNewsletter {

	/**
	 * Send a newsletter
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * <id>
	 * : Newsletter ID to send
	 *
	 * [--test-mode]
	 * : Sends the newsletter as test
	 * ---
	 * default: 1
	 * options:
	 *   - 0 (no test, send for real)
	 *   - 1 (send as test to editors)
	 *   - 2 (send as test to developers)
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels commands send-newsletter run 123
	 */
	public function run( array $args, array $assoc_args ): void {
		$newsletter_id = (int) ( $args[0] ?? 0 );

		/**
		 * @var Newsletter::META_PREVIEW_RECIPIENTS_OPTION_*
		 */
		$test_mode = (string) get_flag_value( $assoc_args, 'test-mode', Newsletter::META_PREVIEW_RECIPIENTS_OPTION_EDITORS );

		$command = new CommandsSendNewsletter( $newsletter_id, $test_mode );
		$command->setLogger( Registry::wordpress() );
		try {
			$result = $command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			exit;
		}

		WP_CLI::success( 'Successfully sent newsletter' );
		WP_CLI::line( wp_json_encode( $result ) ?: '' );
	}
}
