<?php

namespace FDMGLabels\AdminAjax;

use Exception;
use FDMGLabels\Commands\SendArticleToFDMGContentAPI;
use FDMGLabels\Logging\FDMGContentAPI\FDMGContentAPILogger;
use Monolog\Registry;

class SendArticleToFDMGContentAPIController extends AbstractController {

	public const ACTION_NAME = 'fdmg_labels_send_article_to_fdmg_content_api';

	public function register_hooks(): void {
		add_action( sprintf( 'wp_ajax_%s', self::ACTION_NAME ), array( $this, 'handle_request' ) );
	}

	/**
	 * Update the listened count of a tip from Podkite.
	 */
	public function handle_request(): void {
		if ( ! (bool) check_ajax_referer( Init::NONCE, false, false ) ) {
			$this->echo_error_die( __( 'Invalid nonce', 'fdmg-labels-admin' ), 403 );
			exit;
		}

		$post_id = (int) filter_var( $_GET['post_id'] ?? 0, FILTER_VALIDATE_INT );
		$command = new SendArticleToFDMGContentAPI( $post_id );
		$command->setLogger( Registry::wordpress() );

		Registry::wordpress()->info(
			'SendArticleToFDMGContentAPI: Sending post started manually', array(
				'fdmg_logger'         => new FDMGContentAPILogger(),
				'fdmg_logger_post_id' => $post_id,
				'post_id'             => $post_id,
			)
		);
		try {
			$command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			$this->echo_error_die( $e->getMessage(), 500 );
			exit;
		}

		// Redirect to the edit tip url.
		$redirect_to = get_edit_post_link( $post_id, 'raw' ) ?: '';
		wp_safe_redirect( $redirect_to, 302, 'FDMG Lables article sent to FDMG Content API' ); // phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit

		// The wp_die function is used for testing purposes. This will in wp-ajax just use the php die() function.
		wp_die( esc_html( sprintf( 'Redirecting to: %s', $redirect_to ) ), 302 );
	}
}
