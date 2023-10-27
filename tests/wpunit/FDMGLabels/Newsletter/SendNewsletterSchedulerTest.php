<?php

namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;
use DateTime;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;

class SendNewsletterSchedulerTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

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
		wp_delete_post( $this->post->get_id(), true );
	}

	public function test_schedule_once(): void {
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

		$date = new DateTime( '+1 day', wp_timezone() );
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

	public function test_schedule_repetitive(): void {
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
					'weekdays' => array( '0', '1', '2', '3', '4', '5', '6' ),
					'time'     => '15:00:00',
				),
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		// Test scheduled time
		$date_now      = new DateTime( 'now', wp_timezone() );
		$date_today    = ( new DateTime( 'now', wp_timezone() ) )->setTime( 15, 0, 0, 0 );
		$date_tomorrow = ( new DateTime( 'tomorrow', wp_timezone() ) )->setTime( 15, 0, 0, 0 );
		$schedule_date = $date_today > $date_now ? $date_today : $date_tomorrow;
		$scheduled_ts  = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
		$this->assertGreaterThanOrEqual( $schedule_date->getTimestamp() - 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00' );
		$this->assertLessThanOrEqual( $schedule_date->getTimestamp() + 5, $scheduled_ts, 'Sync should be scheduled at the next occurance of 15:00:00' );

		// Test if is logged
		$this->assert_is_logged( $newsletter );

		// Cleanup
		as_unschedule_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id() ), 'fdmg_labels' );
	}

	public function test_dont_schedule(): void {
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

		wp_delete_post( $newsletter, true );
	}

	public function test_maybe_sync_once(): void {
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
					'date'   => ( new DateTime( 'now', wp_timezone() ) )->format( 'Y-m-d H:i:s' ),
				),
			), $newsletter->get_id()
		);

		// Call maybe_sync and see if sync is called.
		$scheduler = $this->getMockBuilder( SendNewsletterScheduler::class )
			->onlyMethods( array( 'maybe_sync', 'sync' ) )
			->getMock();
		$scheduler->expects( $this->once() )
			->method( 'maybe_sync' )
			->with(
				$this->equalTo( $newsletter->get_id() )
			);
		$scheduler->expects( $this->once() )
			->method( 'sync' )
			->with(
				$this->equalTo( $newsletter->get_id() )
			);

		$scheduler->maybe_sync( $newsletter->get_id() );
	}

	// public function test_maybe_sync_repetitive(): void {
	// 	// Create newsletter
	// 	$newsletter = new ii_newsletter(
	// 		$this->factory()->post->create_and_get(
	// 			array(
	// 				'post_title'  => 'Test newsletter selligent',
	// 				'post_type'   => Newsletter::POST_TYPE_NAME,
	// 				'post_status' => 'publish',
	// 			)
	// 		)
	// 	);

	// 	update_field(
	// 		'send', array(
	// 			'action'     => '1',
	// 			'interval'   => 'repetitive',
	// 			'repetitive' => array(
	// 				'weekdays' => array( '1' ),
	// 				'time'     => '15:00:00',
	// 			),
	// 		), $newsletter->get_id()
	// 	);

	// 	// Call maybe_sync and see if sync is called.
	// 	$scheduler = $this->createMock( SendNewsletterScheduler::class );
	// 	$scheduler->expects( $this->once() )
	// 		->method( 'sync' )
	// 		->with(
	// 			$this->equalTo( $newsletter->get_id() )
	// 		);

	// 	$scheduler->maybe_sync( $newsletter->get_id() );
	// }

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
