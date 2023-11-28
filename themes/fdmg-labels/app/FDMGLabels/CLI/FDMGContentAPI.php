<?php

namespace FDMGLabels\CLI;

use Exception;
use FDMGLabels\Commands\SendArticleToFDMGContentAPI;
use FDMGLabels\Logging\FDMGContentAPI\FDMGContentAPILogger;
use Monolog\Registry;
use WP_CLI;

class FDMGContentAPI {

	/**
	 * Send an article.
	 * This creates or updates an article in the FDMG Content API.
	 *
	 * @subcommand send-article
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * <id>
	 * : ID of the post to send
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels fdmg-content-api send-article 123
	 */
	public function send_article( array $args, array $assoc_args ): void {
		$post_id = (int) ( $args[0] ?? 0 );

		$command = new SendArticleToFDMGContentAPI( $post_id );
		$command->setLogger( Registry::wordpress() );

		Registry::wordpress()->info(
			'SendArticleToFDMGContentAPI: Sending post started by CLI', array(
				'fdmg_logger'         => new FDMGContentAPILogger(),
				'fdmg_logger_post_id' => $post_id,
				'post_id'             => $post_id,
			)
		);
		try {
			$response = $command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			exit;
		}

		WP_CLI::success( 'Successfully sent post to FDMG Content API' );
		WP_CLI::line( wp_json_encode( $response ) ?: '' );
	}
}
