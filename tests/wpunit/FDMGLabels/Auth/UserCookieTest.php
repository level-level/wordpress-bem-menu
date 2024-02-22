<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;

class UserCookieTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$_COOKIE = array();
	}

	public function tearDown(): void {
		$_COOKIE = array();

		parent::tearDown();
	}

	public function test_set_cookies(): void {
		// Test an administrator user
		$administrator_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_auth_cookie( $administrator_user->ID, false, true );
		wp_set_current_user( $administrator_user->ID, $administrator_user->user_login );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertEquals( $administrator_user->ID, json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) )->data->id );

		wp_logout();
		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );

		// Test a subscriber user
		$subscriber_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);
		wp_set_auth_cookie( $subscriber_user->ID, false, true );
		wp_set_current_user( $subscriber_user->ID, $subscriber_user->user_login );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$this->assertEquals( $subscriber_user->ID, json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) )->data->id );

		wp_logout();
		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );
	}

	public function test_cookies_delete_on_invalidate(): void {
		// Create user
		$user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);

		wp_set_auth_cookie( $user->ID, false, true );
		wp_set_current_user( $user->ID, $user->user_login );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );

		// Assert cookie is deleted
		$user_cookie = new UserCookie( new AuthCookieHelper() );
		$user_cookie->clear_cookie_if_invalid();
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );

		wp_logout();
	}
}

require_once __DIR__ . '/cookiefaker.php';
