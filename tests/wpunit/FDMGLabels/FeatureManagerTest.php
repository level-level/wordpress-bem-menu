<?php

namespace FDMGLabels;

use Codeception\TestCase\WPTestCase;
use FDMGLabels\FeatureManager;

class FeatureManagerTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->feature_manager = new FeatureManager();
	}

	public function tearDown(): void {
		$this->feature_manager = new FeatureManager();
		delete_field( 'features', 'options' );

		parent::tearDown();
	}

	public function test_can_check_if_feature_is_enabled(): void {
		update_field( 'features', array( 'likes' => true ), 'options' );

		$this->assertTrue( $this->feature_manager->is_enabled( FeatureManager::FEATURE_LIKES ) );
	}

	public function test_can_check_if_feature_is_disabled(): void {
		update_field( 'features', array( 'likes' => false ), 'options' );

		$this->assertFalse( $this->feature_manager->is_enabled( FeatureManager::FEATURE_LIKES ) );
	}
}
