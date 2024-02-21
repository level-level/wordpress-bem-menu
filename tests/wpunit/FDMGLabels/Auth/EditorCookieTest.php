<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;

class EditorCookieTest extends WPTestCase {

	public function test_set_cookie(): void {
		// Test an administrator user
		$administrator_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'administrator',
			)
		);
		wp_set_auth_cookie( $administrator_user->ID, false, true );
		wp_set_current_user( $administrator_user->ID, $administrator_user->user_login );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_editor', $_COOKIE );
		$this->assertEquals( '1', $_COOKIE['fdmg_labels_editor'] );

		wp_logout();
		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );

		// Test a subscriber user
		$subscriber_user = $this->factory()->user->create_and_get(
			array(
				'role' => 'subscriber',
			)
		);
		wp_set_auth_cookie( $subscriber_user->ID, false, true );
		wp_set_current_user( $subscriber_user->ID, $subscriber_user->user_login );

		// Assert cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );

		wp_logout();
		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );
	}

	public function test_clear_on_expired_wp_cookie(): void {
		$_COOKIE['fdmg_labels_editor'] = '1';
		do_action(
			'auth_cookie_expired', array(
				'username'   => 'test',
				'expiration' => time() - HOUR_IN_SECONDS,
				'token'      => 'test',
				'hmac'       => 'test',
				'scheme'     => 'auth',
			)
		);

		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_editor', $_COOKIE );
	}
}

require_once __DIR__ . '/cookiefaker.php';
