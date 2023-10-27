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

class SendNewsletterScheduler {

	public const TRIGGER = 'fdmg_labels_send_newsletter';

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

		$should_send = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_ACTION, $newsletter_id );
		if ( ! $should_send ) {
			return;
		}

		$schedule_date      = new DateTime( 'now', wp_timezone() );
		$customize_schedule = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_SCHEDULE, $newsletter_id );
		if ( $customize_schedule ) {
			$date_string = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_DATE, $newsletter_id );
			$date_obj    = is_string( $date_string ) ? date_create_from_format( 'Y-m-d H:i:s', $date_string, wp_timezone() ) : null;
			if ( $date_obj ) {
				$schedule_date = $date_obj;
			}
		}

		// Schedule single event
		$test_mode = (string) get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_TEST, $newsletter_id );
		as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id, $test_mode ), ActionSchedulerSetup::GROUP );

		// Reset the field values
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_ACTION, '0', $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_TEST, Newsletter::META_SEND_PREVIEW_RECIPIENTS_OPTION_EDITORS, $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_SCHEDULE, '0', $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_DATE, '', $newsletter_id );

		$i18n_util = new I18n();
		if ( $test_mode === Newsletter::META_PREVIEW_RECIPIENTS_OPTION_ALL ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				sprintf(
					'Sending the newsletter has been queued to be sent on %s.',
					$i18n_util->format_date( '', $schedule_date )
				),
				LogLine::LEVEL_INFO
			);
		} else {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				sprintf(
					'Sending the newsletter in testmode %s has been queued to be sent on %s.',
					$test_mode,
					$i18n_util->format_date( '', $schedule_date )
				),
				LogLine::LEVEL_INFO
			);
		}
	}

	/**
	 * Register articles in selligent, and then send the newsletter.
	 *
	 * @param int $newsletter_id
	 * @param Newsletter::META_SEND_PREVIEW_RECIPIENTS_OPTION_* $test_mode
	 */
	public function sync( int $newsletter_id, string $test_mode ): void {
		$command = new CommandsSendNewsletter( $newsletter_id, $test_mode );
		$command->setLogger( Registry::wordpress() );
		try {
			$command->execute();
		} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
		}
	}
}
