<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Utilities\I18n;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use DateTime;
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
		$schedule_date      = new DateTime( 'now', wp_timezone() );
		$schedule_in_future = (bool) ( $once_settings[ Newsletter::META_SEND_ONCE_FUTURE ] ?? false );
		if ( $schedule_in_future ) {
			$date_string = $once_settings[ Newsletter::META_SEND_ONCE_DATE ] ?? null;
			$date_obj    = is_string( $date_string ) ? date_create_from_format( 'Y-m-d H:i:s', $date_string, wp_timezone() ) : null;
			if ( $date_obj ) {
				$schedule_date = $date_obj;
			}
		}

		// Schedule
		$scheduled = as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP, true );
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
		$time_obj    = is_string( $time_string ) ? date_create_from_format( 'H:i:s', $time_string, wp_timezone() ) : null;
		if ( ! $time_obj instanceof DateTime ) {
			return;
		}

		$date_now      = new DateTime( 'now', wp_timezone() );
		$date_today    = ( new DateTime( 'now', wp_timezone() ) )->setTime( (int) $time_obj->format( 'H' ), (int) $time_obj->format( 'i' ), (int) $time_obj->format( 's' ), 0 );
		$date_tomorrow = ( new DateTime( 'tomorrow', wp_timezone() ) )->setTime( (int) $time_obj->format( 'H' ), (int) $time_obj->format( 'i' ), (int) $time_obj->format( 's' ), 0 );
		$schedule_date = $date_today > $date_now ? $date_today : $date_tomorrow;

		// Schedule
		$scheduled = as_schedule_recurring_action( $schedule_date->getTimestamp(), DAY_IN_SECONDS, self::TRIGGER, array( $newsletter_id ), ActionSchedulerSetup::GROUP, true );
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

		if ( ! $this->should_sync( $newsletter_id ) ) {
			return;
		}

		$this->sync( $newsletter_id );
	}

	/**
	 * Check if newsletter is allowed to be sent.
	 * If newsletter is repetitive, but today is not an allowed sending day, dont allow.
	 * Otherwise allow.
	 */
	protected function should_sync( int $newsletter_id ): bool {
		$send_settings = get_field( Newsletter::META_SEND, $newsletter_id ) ?: array();
		$interval      = $send_settings[ Newsletter::META_SEND_INTERVAL ] ?? null;
		if ( $interval === Newsletter::META_SEND_INTERVAL_OPTION_REPETITIVE ) {
			$repetitive_settings     = $send_settings[ Newsletter::META_SEND_REPETITIVE ] ?: array();
			$allowed_weekday_numbers = array_map( 'intval', $repetitive_settings[ Newsletter::META_SEND_REPETITIVE_WEEKDAYS ] ?? array() );
			$today_weekday_number    = (int) ( new DateTime( 'now', wp_timezone() ) )->format( 'w' );
			if ( ! in_array( $today_weekday_number, $allowed_weekday_numbers, true ) ) {
				return false;
			}
		}
		return true;
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
