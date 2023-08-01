<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Utilities\I18n;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use DateTime;
use FDMGLabels\ActionScheduler\ActionSchedulerSetup;
use FDMGLabels\Connectors\Selligent\Client;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use WP_Error;

class SendNewsletter {

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
		$test_mode = (bool) get_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_TEST, $newsletter_id );
		as_schedule_single_action( $schedule_date->getTimestamp(), self::TRIGGER, array( $newsletter_id, $test_mode ), ActionSchedulerSetup::GROUP );

		// Reset the field values
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_ACTION, '0', $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_TEST, '1', $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_SCHEDULE, '0', $newsletter_id );
		update_field( Newsletter::META_SEND . '_' . Newsletter::META_SEND_DATE, '', $newsletter_id );

		$i18n_util = new I18n();
		if ( ! $test_mode ) {
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
					'Sending the newsletter in testmode has been queued to be sent on %s.',
					$i18n_util->format_date( '', $schedule_date )
				),
				LogLine::LEVEL_INFO
			);
		}
	}

	/**
	 * Register articles in selligent, and then send the newsletter.
	 */
	public function sync( int $newsletter_id, bool $test_mode ): void {
		$newsletter = ii_newsletter::get( $newsletter_id );
		if ( ! $newsletter instanceof ii_newsletter ) {
			return;
		}

		if ( ! $test_mode ) {
			$this->selligent_logger->add_post_log( $newsletter_id, 'Sending the newsletter has started.', LogLine::LEVEL_INFO );
		} else {
			$this->selligent_logger->add_post_log( $newsletter_id, 'Sending the newsletter in testmode has started.', LogLine::LEVEL_INFO );
		}

		// Register the articles
		$client      = new Client();
		$article_ids = $newsletter->get_article_ids();
		foreach ( $article_ids as $article_id ) {
			$article = post::get( $article_id );
			if ( ! $article instanceof post ) {
				$this->selligent_logger->add_post_log( $newsletter_id, sprintf( 'The article with ID %d does not exist.', $article_id ), LogLine::LEVEL_ERROR );
				return;
			}

			$response = $client->register_post( $article );
			if ( $response instanceof WP_Error ) {
				$this->selligent_logger->add_post_log(
					$newsletter_id, sprintf(
						'The article %1$d - %2$s could not be registered: %3$s - %4$s.',
						$article->get_id(),
						$article->get_title(),
						(string) $response->get_error_code(),
						$response->get_error_message()
					), LogLine::LEVEL_ERROR
				);
				return;
			}
		}

		// Register the newsletter
		$response = $client->register_newsletter( $newsletter, $test_mode );
		if ( $response instanceof WP_Error ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id, sprintf(
					'The newsletter could not be registered: %1$s - %2$s.',
					(string) $response->get_error_code(),
					$response->get_error_message()
				), LogLine::LEVEL_ERROR
			);
			return;
		}

		// Send the newsletter
		$response = $client->send_newsletter();
		if ( $response instanceof WP_Error ) {
			$this->selligent_logger->add_post_log(
				$newsletter_id, sprintf(
					'The newsletter could not be sent: %1$s - %2$s.',
					(string) $response->get_error_code(),
					$response->get_error_message()
				), LogLine::LEVEL_ERROR
			);
			return;
		}

		if ( ! $test_mode ) {
			$this->selligent_logger->add_post_log( $newsletter_id, 'The newsletter has been sent.', LogLine::LEVEL_SUCCESS );
		} else {
			$this->selligent_logger->add_post_log( $newsletter_id, 'The newsletter has been sent in testmode.', LogLine::LEVEL_SUCCESS );
		}
	}
}
