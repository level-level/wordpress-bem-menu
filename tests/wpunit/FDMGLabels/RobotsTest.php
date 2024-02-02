<?php

namespace FDMGLabels;

use Codeception\TestCase\WPTestCase;
use FDMGLabels\FeatureManager;

class RobotsTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$default_robots_lines_by_yoast = array(
			'# START YOAST BLOCK',
			'# ---------------------------',
			'User-agent: *',
			'Disallow:',
			'',
			'Sitemap: http://fdmg-labels.nl/sitemap_index.xml',
			'# ---------------------------',
			'# END YOAST BLOCK',
		);

		$this->default_robots = implode( PHP_EOL, $default_robots_lines_by_yoast );
		update_field( 'robots_options', array(
			'copyright_notice' => "Please don\'t copy me!\nI said, don\'t copy me!",
			'custom_content' => "# Custom lines\nDisallow: /custom-url/",
		), 'options' );
	}

	public function tearDown(): void {
		delete_field( 'robots_options', 'options' );

		parent::tearDown();
	}

	public function test_overwrite_robots_txt(): void {
		$robots_txt = apply_filters( 'robots_txt', $this->default_robots, get_option( 'blog_public' ) );

		$expected_lines = array(
			'# COPYRIGHT NOTICE',
			'# Please don\'t copy me!',
			'# I said, don\'t copy me!',
			'',
			'# START FDMG BLOCK',
			'-------------------------',
			'# Custom lines',
			'Disallow: /custom-url/',
			'-------------------------',
			'# END FDMG BLOCK',
			'',
			'# START YOAST BLOCK',
			'# ---------------------------',
			'# User-agent: * # Disabled by FDMG Labels',
			'# Disallow: # Disabled by FDMG Labels',
			'',
			'Sitemap: http://fdmg-labels.nl/sitemap_index.xml',
			'# ---------------------------',
			'# END YOAST BLOCK',
		);
		$this->assertEquals( implode( PHP_EOL, $expected_lines ), $robots_txt );
	}
}
