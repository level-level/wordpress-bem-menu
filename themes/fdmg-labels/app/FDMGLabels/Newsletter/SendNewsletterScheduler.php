<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Utilities\I18n;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use DateTimeImmutable;
use DateTimeZone;
use FDMGLabels\ActionScheduler\ActionSchedulerSetup;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;

class SendNewsletterScheduler {

	public const TRIGGER = 'fdmg_labels_send_newsletter';

	protected SelligentLogger $selligent_logger;
	protected NewsletterSenderService $sender_service;

	public function __construct( NewsletterSenderService $sender_service ) {
		$this->selligent_logger = new SelligentLogger();
		$this->sender_service   = $sender_service;
	}

	public function register_hooks(): void {
		add_action( 'save_post', array( $this, 'schedule_sync' ) );
		add_action( self::TRIGGER, array( $this, 'maybe_sync' ), 10, 1 );
	}

	/**
	 * Sends the specified newsletter to Selligent
	 */
	public function schedule_sync( int $newsletter_id ): void {
		if ( get_post_type( $newsletter_id ) !== Newsletter::POST_TYPE_NAME ) {
			return;
		}

		// If is already scheduled, unschedule.
		$was_scheduled = as_has_scheduled_action( self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP );
		if ( $was_scheduled ) {
			as_unschedule_action( self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP );
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				'Sending the newsletter has been removed from queue.',
				LogLine::LEVEL_INFO
			);
		}

		// Determine if newsletter should send.
		$should_send = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_ACTION, $newsletter_id );
		if ( ! $should_send ) {
			return;
		}

		// Schedule again using new settings.
		$interval = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_INTERVAL, $newsletter_id );
		if ( $interval === Newsletter::META_SEND_INTERVAL_OPTION_ONCE ) {
			$this->schedule_once( $newsletter_id );
		} elseif ( $interval === Newsletter::META_SEND_INTERVAL_OPTION_REPETITIVE ) {
			$this->schedule_repetitive( $newsletter_id );
		}
	}

	/**
	 * Schedule sending the newsletter only once, now or at a specific date.
	 */
	protected function schedule_once( int $newsletter_id ): void {
		$once_settings = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_ONCE, $newsletter_id ) ?: array();

		// Determine the date to schedule
		$schedule_date      = new DateTimeImmutable( 'now', wp_timezone() );
		$schedule_in_future = (bool) ( $once_settings[ Newsletter::META_SEND_ONCE_FUTURE ] ?? false );
		if ( $schedule_in_future ) {
			$date_string = $once_settings[ Newsletter::META_SEND_ONCE_DATE ] ?? null;
			$date_obj    = is_string( $date_string ) ? date_create_immutable_from_format( 'Y-m-d H:i:s', $date_string, wp_timezone() ) : null;
			if ( $date_obj ) {
				$schedule_date = $date_obj;
			}
		}

		// Schedule
		$scheduled = as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP );
		if ( ! $scheduled ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				'Failed to schedule sending newsletter once.',
				LogLine::LEVEL_ERROR
			);
			return;
		}

		$i18n_util = new I18n();
		$this->selligent_logger->add_post_log(
			$newsletter_id,
			sprintf(
				'Sending the newsletter has been queued to be sent once on %s.',
				$i18n_util->format_date( '', $schedule_date )
			),
			LogLine::LEVEL_INFO
		);
	}

	/**
	 * Schedule sending the newsletter repetitive, at a specific time.
	 */
	protected function schedule_repetitive( int $newsletter_id ): void {
		$repetitive_settings = get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_REPETITIVE, $newsletter_id ) ?: array();

		// Determine the date to schedule first run
		$time_string = $repetitive_settings[ Newsletter::META_SEND_REPETITIVE_TIME ] ?? null;
		$time_obj    = is_string( $time_string ) ? date_create_immutable_from_format( 'H:i:s', $time_string, wp_timezone() ) : null;
		if ( ! $time_obj instanceof DateTimeImmutable ) {
			return;
		}

		// Determine the schedule to repeat
		$allowed_weekday_numbers = array_map( 'intval', $repetitive_settings[ Newsletter::META_SEND_REPETITIVE_WEEKDAYS ] ?? array() );
		if ( empty( $allowed_weekday_numbers ) ) {
			return;
		}

		$cron_schedule = $this->determine_cron_schedule(
			$time_obj,
			$allowed_weekday_numbers,
		);

		// Schedule
		$scheduled = as_schedule_cron_action( time(), $cron_schedule, self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP );
		if ( ! $scheduled ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id,
				'Failed to schedule sending newsletter repetitive.',
				LogLine::LEVEL_ERROR
			);
			return;
		}

		$this->selligent_logger->add_post_log(
			$newsletter_id,
			'Sending the newsletter has been queued to be sent repetitive.',
			LogLine::LEVEL_INFO
		);
	}

	/**
	 * Determine the cron schedule for sending the newsletter repetitive.
	 * It makes sure to convert the time to UTC, as all crontab actions run on UTC timezone.
	 * @param DateTimeImmutable $time_obj The time to send the newsletter in local time.
	 * @param non-empty-array<int> $weekday_numbers
	 * @return string
	 */
	protected function determine_cron_schedule( DateTimeImmutable $time_obj, array $weekday_numbers ): string {
		// Cron schedule always needs to be in UTC timezone.
		$utc_time_obj = $time_obj->setTimezone( new DateTimeZone( 'UTC' ) );
		return sprintf(
			'%d %d * * %s',
			(int) $utc_time_obj->format( 'i' ),
			(int) $utc_time_obj->format( 'H' ),
			implode( ',', $weekday_numbers )
		);
	}

	protected function reset_send_settings( int $newsletter_id ): void {
		delete_field( Newsletter::META_SEND, $newsletter_id );
	}

	/**
	 * Maybe sync the newsletter, based on:
	 * - If it's daily, just sync.
	 * - If it's weekly, sync if today is allowed as a day to send.
	 */
	public function maybe_sync( int $newsletter_id ): void {
		// If newsletter doesn't exist, remove cronjob.
		$newsletter = ii_newsletter::get( $newsletter_id );
		if ( ! $newsletter ) {
			as_unschedule_action( self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP );
			return;
		}

		$this->sync( $newsletter_id );
	}

	protected function sync( int $newsletter_id ): void {
		// Remove the send settings if it's a newsletter that's sent once.
		$send_settings = get_field( Newsletter::META_SEND, $newsletter_id ) ?: array();
		$interval      = $send_settings[ Newsletter::META_SEND_INTERVAL ] ?? null;
		if ( $interval === Newsletter::META_SEND_INTERVAL_OPTION_ONCE ) {
			// Reset the field values for send settings
			delete_field( Newsletter::META_SEND, $newsletter_id );
		}

		// Actually send the newsletter
		$this->sender_service->send( $newsletter_id, Newsletter::META_PREVIEW_RECIPIENTS_OPTION_ALL );
	}
}
