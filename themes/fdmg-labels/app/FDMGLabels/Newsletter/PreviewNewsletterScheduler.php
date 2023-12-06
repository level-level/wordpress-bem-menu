<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Utilities\I18n;
use DateTime;
use FDMGLabels\ActionScheduler\ActionSchedulerSetup;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;

class PreviewNewsletterScheduler {

	public const TRIGGER = 'fdmg_labels_preview_newsletter';

	protected SelligentLogger $selligent_logger;
	protected NewsletterSenderService $sender_service;

	public function __construct( NewsletterSenderService $sender_service ) {
		$this->selligent_logger = new SelligentLogger();
		$this->sender_service   = $sender_service;
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
		$schedule_date   = new DateTime( 'now', wp_timezone() );
		$recipient_group = (string) get_field( Newsletter::META_PREVIEW . '_' . Newsletter::META_PREVIEW_RECIPIENTS, $newsletter_id );
		$scheduled       = as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id, $recipient_group ), ActionSchedulerSetup::GROUP );
		if ( ! $scheduled ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				'Failed to schedule newsletter preview.',
				LogLine::LEVEL_ERROR
			);
			return;
		}

		// Reset the field values
		delete_field( Newsletter::META_PREVIEW, $newsletter_id );

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
	 * @param Newsletter::META_PREVIEW_RECIPIENTS_OPTION_* $recipients_group
	 */
	public function sync( int $newsletter_id, string $recipients_group ): void {
		$this->sender_service->send( $newsletter_id, $recipients_group );
	}
}
