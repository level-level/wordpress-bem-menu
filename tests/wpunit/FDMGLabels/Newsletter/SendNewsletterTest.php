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

class SendNewsletterTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test tip selligent',
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

	public function test_schedule_now(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);
		update_field( 'send_action', '1', $newsletter->get_id() );
		update_field( 'send_test', '0', $newsletter->get_id() );
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_testmode = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled          = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_testmode, 'Sync should not be scheduled in testmode' );
		$this->assertTrue( $scheduled, 'Sync should be scheduled' );

		$this->assert_send_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, false );

		wp_delete_post( $newsletter, true );
		as_unschedule_action( $scheduled, 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
	}

	public function test_schedule_testmode_now(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);
		update_field( 'send_action', '1', $newsletter->get_id() );
		update_field( 'send_test', '1', $newsletter->get_id() );
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_testmode = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled          = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
		$this->assertTrue( $scheduled_testmode, 'Sync should be scheduled in testmode' );
		$this->assertFalse( $scheduled, 'Sync should not be scheduled' );

		$this->assert_send_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, true );

		wp_delete_post( $newsletter, true );
		as_unschedule_action( $scheduled_testmode, 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
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

		$scheduled_testmode = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled          = as_has_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_testmode, 'Sync should not be scheduled in testmode' );
		$this->assertFalse( $scheduled, 'Sync should not be scheduled' );

		wp_delete_post( $newsletter, true );
	}

	public function test_schedule_custom_date(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);
		update_field( 'send_action', '1', $newsletter->get_id() );
		update_field( 'send_test', '1', $newsletter->get_id() );
		update_field( 'send_schedule', '1', $newsletter->get_id() );
		update_field( 'send_date', '2040-03-01 09:30:00', $newsletter->get_id() );
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_testmode = as_next_scheduled_action( 'fdmg_labels_send_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$this->assertEquals( ( new DateTime( '2040-03-01 09:30:00', wp_timezone() ) )->getTimestamp(), $scheduled_testmode, 'Sync should be scheduled at the selected future timestamp' );
	}

	protected function assert_send_meta_reset( ii_newsletter $newsletter ): void {
		$this->assertEquals( '0', get_field( 'send_action', $newsletter->get_id() ), 'Send switch should be set to false' );
		$this->assertEquals( '1', get_field( 'send_test', $newsletter->get_id() ), 'Testmode switch should be set to true' );
		$this->assertEquals( '0', get_field( 'send_schedule', $newsletter->get_id() ), 'Custom schedule switch should be set to false' );
		$this->assertEmpty( get_field( 'send_date', $newsletter->get_id() ), 'Custom schedule date should be empty' );
	}

	protected function assert_is_logged( ii_newsletter $newsletter, bool $testmode ) {
		$logger    = new SelligentLogger();
		$log_lines = $logger->get_post_logs( $newsletter->get_id() );

		$this->assertInstanceOf( LogLine::class, $log_lines[0], 'A log line should have been added.' );
		$this->assertEquals( 'info', $log_lines[0]->get_level(), 'The added log line should have error level \'info\'.' );

		if ( $testmode ) {
			$this->assertStringContainsString( 'test', $log_lines[0]->get_message(), 'The added log line should specify it is a test.' );
		}
	}
}
