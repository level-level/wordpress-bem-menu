<?php

namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use lucatume\WPBrowser\TestCase\WPTestCase;
use FDMGLabels\Logging\LogLine;
use FDMGLabels\Logging\Selligent\SelligentLogger;

class PreviewNewsletterSchedulerTest extends WPTestCase {

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

	public function test_newsletter_when_preview_enabled_then_schedule(): void {
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
		update_field(
			'preview', array(
				'action'     => '1',
				'recipients' => '1',
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_1 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled_2 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );
		$this->assertTrue( $scheduled_1, 'Sync should be scheduled in testmode 1' );
		$this->assertFalse( $scheduled_2, 'Sync should not be scheduled in testmode 2' );

		$this->assert_preview_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, '1' );
		as_unschedule_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );

		// Testmode 2
		update_field(
			'preview', array(
				'action'     => '1',
				'recipients' => '2',
			), $newsletter->get_id()
		);
		do_action( 'save_post', $newsletter->get_id(), $newsletter->get_object(), true );

		$scheduled_1 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '1' ), 'fdmg_labels' );
		$scheduled_2 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_1, 'Sync should not be scheduled in testmode 1' );
		$this->assertTrue( $scheduled_2, 'Sync should be scheduled in testmode 2' );

		$this->assert_preview_meta_reset( $newsletter );
		$this->assert_is_logged( $newsletter, '2' );
		as_unschedule_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '2' ), 'fdmg_labels' );

		wp_delete_post( $newsletter, true );
	}

	public function test_newsletter_when_preview_disabled_then_dont_schedule(): void {
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
		$scheduled_2 = as_has_scheduled_action( 'fdmg_labels_preview_newsletter', array( $newsletter->get_id(), '0' ), 'fdmg_labels' );
		$this->assertFalse( $scheduled_1, 'Sync should not be scheduled' );
		$this->assertFalse( $scheduled_2, 'Sync should not be scheduled' );

		wp_delete_post( $newsletter, true );
	}

	protected function assert_preview_meta_reset( ii_newsletter $newsletter ): void {
		$this->assertEmpty( get_field( 'preview', $newsletter->get_id() ), 'Preview settings should be reset.' );
	}

	protected function assert_is_logged( ii_newsletter $newsletter, string $testmode = '0' ) {
		$logger    = new SelligentLogger();
		$log_lines = $logger->get_post_logs( $newsletter->get_id() );

		$log_lines = array_filter(
			$log_lines, function ( LogLine $log_line ) use ( $testmode ): bool {
				if ( $log_line->get_level() !== 'info' ) {
					return false;
				}

				return str_contains( $log_line->get_message(), 'recipient group ' . $testmode );
			}
		);

		$this->assertNotEmpty( $log_lines, sprintf( 'Log lines should contain a log for recipient group %s', $testmode ) );
	}
}
