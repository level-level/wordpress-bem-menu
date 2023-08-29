<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;

class FDMGIdentificationTest extends WPTestCase {

	public function test_set_cookies(): void {
		// Test an administrator user
		$administrator_user = $this->factory()->user->create_and_get(
			array(
				'role'       => 'administrator',
				'meta_input' => array(
					'fdmg_user_id'         => '123456789',
					'fdmg_organisation_id' => '987654321',
				),
			)
		);
		wp_set_auth_cookie( $administrator_user->ID, false, true );
		wp_set_current_user( $administrator_user->ID, $administrator_user->user_login );

		// Assert cookies are set
		$this->assertArrayHasKey( 'fdmg_user_id', $_COOKIE );
		$this->assertArrayHasKey( 'fdmg_organisation_id', $_COOKIE );
		$this->assertEquals( '123456789', $_COOKIE['fdmg_user_id'] );
		$this->assertEquals( '987654321', $_COOKIE['fdmg_organisation_id'] );

		wp_logout();
		// Assert no cookies are set
		$this->assertArrayNotHasKey( 'fdmg_user_id', $_COOKIE );
		$this->assertArrayNotHasKey( 'fdmg_organisation_id', $_COOKIE );

		// Test a subscriber user
		$subscriber_user = $this->factory()->user->create_and_get(
			array(
				'role'       => 'subscriber',
				'meta_input' => array(
					'fdmg_user_id'         => '999999',
					'fdmg_organisation_id' => '111111',
				),
			)
		);
		wp_set_auth_cookie( $subscriber_user->ID, false, true );
		wp_set_current_user( $subscriber_user->ID, $subscriber_user->user_login );

		// Assert cookies are set
		$this->assertArrayHasKey( 'fdmg_user_id', $_COOKIE );
		$this->assertArrayHasKey( 'fdmg_organisation_id', $_COOKIE );
		$this->assertEquals( '999999', $_COOKIE['fdmg_user_id'] );
		$this->assertEquals( '111111', $_COOKIE['fdmg_organisation_id'] );

		wp_logout();
		// Assert no cookies are set
		$this->assertArrayNotHasKey( 'fdmg_user_id', $_COOKIE );
		$this->assertArrayNotHasKey( 'fdmg_organisation_id', $_COOKIE );
	}
}

require_once __DIR__ . '/cookiefaker.php';
