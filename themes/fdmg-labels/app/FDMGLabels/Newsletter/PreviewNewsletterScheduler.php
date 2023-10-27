<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Utilities\I18n;
use DateTime;
use Exception;
use FDMGLabels\ActionScheduler\ActionSchedulerSetup;
use FDMGLabels\Commands\SendNewsletter as CommandsSendNewsletter;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use Monolog\Registry;

class PreviewNewsletterScheduler {

	public const TRIGGER = 'fdmg_labels_preview_newsletter';

	protected SelligentLogger $selligent_logger;

	public function __construct() {
		$this->selligent_logger = new SelligentLogger();
	}

	public function register_hooks(): void {
		add_action( 'save_post', array( $this, 'schedule_sync' ) );
		add_action( self::TRIGGER, array( $this, 'sync' ), 10, 2 );
	}

	/**
	 * Sends the specified newsletter to Selligent
	 */
	public function schedule_sync( int $newsletter_id ): void {
		if ( get_post_type( $newsletter_id ) !== Newsletter::POST_TYPE_NAME ) {
			return;
		}

		$should_send = get_field( Newsletter::META_PREVIEW . '_' . Newsletter::META_PREVIEW_ACTION, $newsletter_id );
		if ( ! $should_send ) {
			return;
		}

		// Schedule single event
		$schedule_date = new DateTime( 'now', wp_timezone() );
		$recipient_group = (string) get_field( Newsletter::META_PREVIEW . '_' . Newsletter::META_PREVIEW_RECIPIENTS, $newsletter_id );
		$scheduled = as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id, $recipient_group ), ActionSchedulerSetup::GROUP );
		if ( ! $scheduled ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				'Failed to schedule newsletter preview.',
				LogLine::LEVEL_ERROR
			);
			return;
		}

		// Reset the field values
		update_field( Newsletter::META_PREVIEW . '_' . Newsletter::META_PREVIEW_ACTION, '0', $newsletter_id );
		update_field( Newsletter::META_PREVIEW . '_' . Newsletter::META_PREVIEW_RECIPIENTS, Newsletter::META_PREVIEW_RECIPIENTS_OPTION_EDITORS, $newsletter_id );

		$i18n_util = new I18n();
		$this->selligent_logger->add_post_log(
			$newsletter_id,
			sprintf(
				'Sending the newsletter in testmode to recipient group %s has been queued to be sent on %s.',
				$recipient_group,
				$i18n_util->format_date( '', $schedule_date )
			),
			LogLine::LEVEL_INFO
		);
	}

	/**
	 * Register articles in selligent, and then send the newsletter.
	 *
	 * @param int $newsletter_id
	 * @param Newsletter::META_SEND_PREVIEW_RECIPIENTS_OPTION_* $test_mode
	 */
	public function sync( int $newsletter_id, string $recipients_group ): void {
		$command = new CommandsSendNewsletter( $newsletter_id, $recipients_group );
		$command->setLogger( Registry::wordpress() );
		try {
			$command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
}
