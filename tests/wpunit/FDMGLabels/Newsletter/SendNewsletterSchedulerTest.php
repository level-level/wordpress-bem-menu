<?php

namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use DateTimeImmutable;
use DateTimeZone;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SendNewsletterSchedulerTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->timezone_string_backup = get_option( 'timezone_string' );
		$this->gmt_offset_backup      = get_option( 'gmt_offset' );

		$this->post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test article selligent',
					'post_type'   => PostTypesPost::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);
	}

	public function tearDown(): void {
		parent::tearDown();

		update_option( 'timezone_string', $this->timezone_string_backup );
		update_option( 'gmt_offset', $this->gmt_offset_backup );

		wp_delete_post( $this->post->get_id(), true );
	}

	public function test_newsletter_when_sending_once_then_schedule_single(): void {
		// Create newsletter
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		$date = new DateTimeImmutable( '+1 day', wp_timezone() );
		update_field(
			'send', array(
				'action'   => '1',
				'interval' => 'once',
				'once'     => array(
					'future' => 1,
					'date'   => $date->format( 'Y-m-d H:i:s' ),
				),
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		// Test scheduled time
		$scheduled_ts = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertEquals( $date->getTimestamp(), $scheduled_ts, 'Sync should be scheduled at specific timestamp' );

		// Test if is logged
		$this->assert_is_logged( $newsletter );

		// Cleanup
		as_unschedule_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
	}

	public function test_newsletter_when_sending_repetitive_then_schedule_recurring(): void {
		$date_3_days_from_now = ( new DateTimeImmutable( '+3 days', wp_timezone() ) )->setTime( 15, 0, 0, 0 );

		// Create newsletter
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field(
			'send', array(
				'action'     => '1',
				'interval'   => 'repetitive',
				'repetitive' => array(
					'weekdays' => array( $date_3_days_from_now->format( 'w' ) ),
					'time'     => $date_3_days_from_now->format( 'H:i:s' ),
				),
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		// Test scheduled time
		$scheduled_ts = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertGreaterThanOrEqual( $date_3_days_from_now->getTimestamp() - 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00' );
		$this->assertLessThanOrEqual( $date_3_days_from_now->getTimestamp() + 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00' );

		// Test if is logged
		$this->assert_is_logged( $newsletter );

		// Cleanup
		as_unschedule_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
	}

	public function test_newsletter_when_sending_disabled_then_dont_schedule(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		$scheduled = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertFalse( $scheduled, 'Sync should not be scheduled' );

		wp_delete_post( $newsletter->get_id(), true );
	}

	public function test_newsletter_when_sending_once_then_sync(): void {
		// Create newsletter
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field(
			'send', array(
				'action'   => '1',
				'interval' => 'once',
				'once'     => array(
					'future' => 1,
					'date'   => ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
				),
			), $newsletter->get_id()
		);

		$service = $this->createMock( NewsletterSenderService::class );
		$service->expects( $this->once() )
			->method( 'send' )
			->with(
				$this->equalTo( $newsletter->get_id() ),
				$this->equalTo( '0' )
			);

		$scheduler = new SendNewsletterScheduler( $service );
		$scheduler->maybe_sync( $newsletter->get_id() );

		$this->assertEmpty( get_field( 'send', $newsletter->get_id() ) );
	}

	public function test_newsletter_when_sending_repetitive_on_correct_day_then_sync(): void {
		// Create newsletter
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		$current_weekday   = ( new DateTimeImmutable( 'now', wp_timezone() ) )->format( 'w' );
		$scheduled_weekday = $current_weekday;

		update_field(
			'send', array(
				'action'     => '1',
				'interval'   => 'repetitive',
				'repetitive' => array(
					'weekdays' => array( $scheduled_weekday ),
					'time'     => '15:00:00',
				),
			), $newsletter->get_id()
		);

		// Call maybe_sync and see if sync is called.
		$service = $this->createMock( NewsletterSenderService::class );
		$service->expects( $this->once() )
			->method( 'send' )
			->with(
				$this->equalTo( $newsletter->get_id() ),
				$this->equalTo( '0' )
			);

		$scheduler = new SendNewsletterScheduler( $service );
		$scheduler->maybe_sync( $newsletter->get_id() );

		$this->assertNotEmpty( get_field( 'send', $newsletter->get_id() ) );
	}

	public function test_rescheduled_on_timezone_change(): void {
		$date_3_days_from_now_utc_plus_one = ( new DateTimeImmutable( '+3 days', new DateTimeZone( 'Etc/GMT+1' ) ) )->setTime( 15, 0, 0, 0 );
		$date_3_days_from_now_utc_plus_two = ( new DateTimeImmutable( '+3 days', new DateTimeZone( 'Etc/GMT+2' ) ) )->setTime( 15, 0, 0, 0 );

		// Change timezone
		update_option( 'timezone_string', 'Etc/GMT+1' );
		update_option( 'gmt_offset', 1 );

		// Set offset to current
		update_option( 'fdmg_labels_newsletter_timezone_offset', $date_3_days_from_now_utc_plus_one->getOffset() );

		// Create newsletter
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		update_field(
			'send', array(
				'action'     => '1',
				'interval'   => 'repetitive',
				'repetitive' => array(
					'weekdays' => array( $date_3_days_from_now_utc_plus_one->format( 'w' ) ),
					'time'     => $date_3_days_from_now_utc_plus_one->format( 'H:i:s' ),
				),
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		// Test scheduled time
		$scheduled_ts = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertGreaterThanOrEqual( $date_3_days_from_now_utc_plus_one->getTimestamp() - 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00 UTC+1' );
		$this->assertLessThanOrEqual( $date_3_days_from_now_utc_plus_one->getTimestamp() + 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00 UTC+1' );

		// Change timezone
		update_option( 'timezone_string', 'Etc/GMT+2' );
		update_option( 'gmt_offset', 2 );

		// Call reschedule_syncs_on_timezone_offset_change
		$service   = $this->createMock( NewsletterSenderService::class );
		$scheduler = new SendNewsletterScheduler( $service );
		$scheduler->maybe_reschedule_syncs_on_timezone_offset_change();

		// Test new offset is stored
		$this->assertEquals( $date_3_days_from_now_utc_plus_two->getOffset(), (int) get_option( 'fdmg_labels_newsletter_timezone_offset' ) );

		// Test scheduled time
		$scheduled_ts = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertGreaterThanOrEqual( $date_3_days_from_now_utc_plus_two->getTimestamp() - 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00 UTC+2' );
		$this->assertLessThanOrEqual( $date_3_days_from_now_utc_plus_two->getTimestamp() + 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00 UTC+2' );
	}

	protected function assert_is_logged( ii_newsletter $newsletter, string $testmode = '0' ) {
		$logger    = new SelligentLogger();
		$log_lines = $logger->get_post_logs( $newsletter->get_id() );

		$log_lines = array_filter(
			$log_lines, function ( LogLine $log_line ): bool {
				if ( $log_line->get_level() !== 'info' ) {
					return false;
				}

				return ! str_contains( $log_line->get_message(), 'recipient group' );
			}
		);

		$this->assertNotEmpty( $log_lines, sprintf( 'Log lines should contain a log for recipient group %s', $testmode ) );
	}
}
