<?php

namespace FDMGLabels\Connectors\FDMGContentAPI;

use Codeception\TestCase\WPTestCase;

class ConfigurationTest extends WPTestCase {

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

	public function test_configuration(): void {
		$configuration = new Configuration();
		$this->assertTrue( $configuration->is_enabled() );
		$this->assertSame( 'ENG', $configuration->get_article_hook_name() );

		$configuration_model = $configuration->get_configuration();
		$this->assertSame( 'https://dev-api.ota.fdmg.org/Distributor/level2', $configuration_model->getHost() );
		$this->assertSame( '1234567890', $configuration_model->getApiKey( 'x-api-key' ) );

	}
}
