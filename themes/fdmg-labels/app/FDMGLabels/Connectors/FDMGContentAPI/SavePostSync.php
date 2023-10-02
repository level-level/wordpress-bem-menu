<?php
namespace FDMGLabels\Connectors\FDMGContentAPI;

use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\post;
use Exception;
use FDMGLabels\Commands\SendArticleToFDMGContentAPI;
use FDMGLabels\Connectors\FDMGContentAPI\Configuration as FDMGContentAPIConfiguration;
use FDMGLabels\Logging\FDMGContentAPI\FDMGContentAPILogger;
use Monolog\Registry;
use WP_Post;

class SavePostSync {

	public const ACTION = 'fdmg_content_api_post_article';

	public function register_hooks(): void {
		add_action( 'save_post', array( $this, 'maybe_schedule' ), 10, 2 );
		add_action( self::ACTION, array( $this, 'execute' ) );
	}

	/**
	 * If a post is of post type post and the FDMG Content API is enabled,
	 * schedule a cron job to send the post to the FDMG Content API.
	 *
	 * @param integer $post_id
	 * @param WP_Post $post
	 * @return void
	 */
	public function maybe_schedule( int $post_id, WP_Post $post ): void {
		$object = Objects::get_instance()->get_object( $post );
		if ( ! $object instanceof post ) {
			return;
		}

		if ( ! ( new FDMGContentAPIConfiguration() )->is_enabled() ) {
			return;
		}

		if ( as_has_scheduled_action( self::ACTION, array( $post_id ), 'fdmg-labels' ) ) {
			return;
		}

		$action_id = as_schedule_single_action( time(), 'fdmg_content_api_post_article', array( $post_id ), 'fdmg-labels' );
		if ( ! $action_id ) {
			return;
		}
		Registry::wordpress()->info(
			'SendArticleToFDMGContentAPI: Sending post scheduled', array(
				'fdmg_logger'         => new FDMGContentAPILogger(),
				'fdmg_logger_post_id' => $object->get_id(),
				'post_id'             => $object->get_id(),
			)
		);
	}

	public function execute( int $post_id ): void {
		$command = new SendArticleToFDMGContentAPI( $post_id );
		$command->setLogger( Registry::wordpress() );
		Registry::wordpress()->info(
			'SendArticleToFDMGContentAPI: Sending post started by cron', array(
				'fdmg_logger'         => new FDMGContentAPILogger(),
				'fdmg_logger_post_id' => $post_id,
				'post_id'             => $post_id,
			)
		);
		try {
			$command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// Do nothing
		}
	}
}
