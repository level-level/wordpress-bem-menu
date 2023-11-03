<?php

namespace FDMGLabels\Newsletter;

use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use Codeception\TestCase\WPTestCase;
use DateTime;
use DateTimeImmutable;
use FDMGLabels\Connectors\Selligent\Settings\Settings;

class MergeTagsTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		// Update newsletter settings
		update_field(
			'selligent', array(
				'newsletter' => array(
					'types' => array(
						array(
							'type'           => 'WEEKLY',
							'article_groups' => array(
								array(
									'name' => 'Articles',
								),
							),
						),
					),
				),
			), 'options'
		);
		Settings::get_instance()->clear_cache();

		// Create a post
		$this->post = new post(
			$this->factory()->post->create_and_get(
				array(
					'post_title'  => 'Test article selligent',
					'post_type'   => 'post',
					'post_status' => 'publish',
				)
			)
		);

		// Create the newsletter
		$this->newsletter = new ii_newsletter(
			$this->factory()->post->create_and_get(
				array(
					'post_title'    => 'Test newsletter selligent',
					'post_type'     => 'ii_newsletter',
					'post_status'   => 'publish',
					'post_date'     => '2020-01-01 00:00:00',
					'post_date_gmt' => '2020-01-01 00:00:00',
				)
			)
		);
		update_field( 'subject', 'Test subject {date-now}', $this->newsletter->get_id() );
		update_field( 'pre_header', 'Test pre header {date-now-format-l j F Y H:i:s}', $this->newsletter->get_id() );
		update_field( 'introduction', 'Test introduction {first-article-title}', $this->newsletter->get_id() );
		update_field( 'type', 'WEEKLY', $this->newsletter->get_id() );
		update_field(
			'article_groups_weekly', array(
				'article_group_0' => array(
					'articles' => array(
						array( 'article' => $this->post->get_id() ),
					),
				),
			),
			$this->newsletter->get_id()
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		wp_delete_post( $this->post->get_id(), true );
		wp_delete_post( $this->newsletter->get_id(), true );
	}

	public function test_replaced_merge_tags(): void {
		$date_now = new DateTimeImmutable( 'now', wp_timezone() );

		$this->assertStringContainsString(
			date_i18n( get_option( 'date_format' ), $date_now ),
			$this->newsletter->get_subject()
		);
		$this->assertStringContainsString(
			date_i18n( 'l j F Y H:i:s', $date_now ),
			$this->newsletter->get_pre_header()
		);
		$this->assertStringContainsString(
			'Test article selligent',
			$this->newsletter->get_introduction()
		);
	}
}
