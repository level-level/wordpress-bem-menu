<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;

class UserCookiesTest extends WPTestCase {

	public function test_set_cookies(): void {
		// Test an administrator user
		$administrator_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_auth_cookie( $administrator_user->ID, false, true );
		wp_set_current_user( $administrator_user->ID, $administrator_user->user_login );

		// Assert cookies are set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertArrayHasKey( 'fdmg_labels_editor', $_COOKIE );
		$this->assertEquals( $administrator_user->ID, json_decode( $_COOKIE['fdmg_labels_user'] )->id );
		$this->assertEquals( '1', $_COOKIE['fdmg_labels_editor'] );

		wp_logout();
		// Assert no cookies are set
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );

		// Test a subscriber user
		$subscriber_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);
		wp_set_auth_cookie( $subscriber_user->ID, false, true );
		wp_set_current_user( $subscriber_user->ID, $subscriber_user->user_login );

		// Assert cookies are set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );
		$this->assertEquals( $subscriber_user->ID, json_decode( $_COOKIE['fdmg_labels_user'] )->id );

		wp_logout();
		// Assert no cookies are set
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );
	}
}

require_once __DIR__ . '/cookiefaker.php';
