<?php

namespace FDMGLabels\Connectors\FDMGContentAPI;

use Clarkson_Core\WordPress_Object\attachment;
use Clarkson_Core\WordPress_Object\fdmg_genre;
use Clarkson_Core\WordPress_Object\fdmg_section;
use Clarkson_Core\WordPress_Object\post;
use Clarkson_Core\WordPress_Object\user;
use DateTime;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SavePostSyncTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		update_field(
			'fdmg_content_api', array(
				'enabled'         => true,
				'domain'          => 'dev-api.ota.fdmg.org',
				'api_key'         => '1234567890',
				'site_connection' => 'energeia',
			), 'options'
		);
	}

	public function tearDown(): void {
		delete_field( 'fdmg_content_api', 'options' );

		parent::tearDown();
	}

	public function test_schedule(): void {
		$post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test post',
					'post_name'   => 'test-post',
					'post_type'   => 'post',
					'post_status' => 'publish',
				)
			)
		);

		$save_post_sync = new SavePostSync();
		$save_post_sync->maybe_schedule( $post->get_id(), $post->get_object() );

		$this->assertTrue( as_has_scheduled_action( 'fdmg_content_api_post_article', array( $post->get_id() ), 'fdmg-labels' ) );
		as_unschedule_action( 'fdmg_content_api_post_article', array( $post->get_id() ), 'fdmg-labels' );
	}
}
