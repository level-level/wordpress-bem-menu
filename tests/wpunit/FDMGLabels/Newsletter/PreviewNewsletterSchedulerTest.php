<?php

namespace FDMGLabels\Newsletter;

use Clarkson\Theme\ACF;
use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;
use DateTime;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;

class PreviewNewsletterSchedulerTest extends WPTestCase {

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

	public function test_schedule(): void {
		$newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test newsletter selligent',
					'post_type'   => Newsletter::POST_TYPE_NAME,
					'post_status' => 'publish',
				)
			)
		);

		// Testmode 1
		update_field( 'field_3782ce41_field_3782ce41', '1', $newsletter->get_id() );
		update_field( 'field_3782ce41_field_69ebbcf6', '1', $newsletter->get_id() );
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_1 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled_2          = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );
		$this->assertTrue( $scheduled_1, 'Sync should be scheduled in testmode 1' );
		$this->assertFalse( $scheduled_2, 'Sync should not be scheduled in testmode 2' );

		$this->assert_preview_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, '1' );
		as_unschedule_action( $scheduled_1, 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );

		// Testmode 2
		update_field( ACF::get_generated_acf_key( 'preview_action', 'field', 'newsletter_meta' ), '1', $newsletter->get_id() );
		update_field( ACF::get_generated_acf_key( 'preview_recipients', 'field', 'newsletter_meta' ), '2', $newsletter->get_id() );
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_1 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled_2          = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_1, 'Sync should be scheduled in testmode 1' );
		$this->assertTrue( $scheduled_2, 'Sync should not be scheduled in testmode 2' );

		$this->assert_preview_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, '1' );
		as_unschedule_action( $scheduled_1, 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );

		wp_delete_post( $newsletter, true );
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

		$scheduled_1 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled_2          = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_1, 'Sync should not be scheduled' );
		$this->assertFalse( $scheduled_2, 'Sync should not be scheduled' );

		wp_delete_post( $newsletter, true );
	}

	protected function assert_preview_meta_reset( ii_newsletter $newsletter ): void {
		$this->assertEquals( '0', get_field( 'preview_action', $newsletter->get_id() ), 'Preview switch should be set to false' );
		$this->assertEquals( '1', get_field( 'preview_recipients', $newsletter->get_id() ), 'Recipients should be set to 1' );
	}

	protected function assert_is_logged( ii_newsletter $newsletter, string $testmode = '0' ) {
		$logger    = new SelligentLogger();
		$log_lines = $logger->get_post_logs( $newsletter->get_id() );

		$this->assertInstanceOf( LogLine::class, $log_lines[0], 'A log line should have been added.' );
		$this->assertEquals( 'info', $log_lines[0]->get_level(), 'The added log line should have error level \'info\'.' );

		if ( $testmode === '0' ) {
			$this->assertStringNotContainsString( 'recipient group', $log_lines[0]->get_message(), 'The added log line should specify it is not a test.' );
		} else {
			$this->assertStringContainsString( 'recipient group ' . $testmode, $log_lines[0]->get_message(), 'The added log line should specify it is a test.' );
		}
	}
}
