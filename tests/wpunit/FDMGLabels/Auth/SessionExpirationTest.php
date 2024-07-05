<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;
use lucatume\WPBrowser\TestCase\WPTestCase;

class SessionExpirationTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$_COOKIE = array();
	}

	public function tearDown(): void {
		$_COOKIE = array();

		parent::tearDown();
	}

	public function test_visitor_user(): void {
		$user = new user(
			$this->factory()->user->create_and_get(
				array(
					'role' => 'fdmg_account_holder',
				)
			)
		);

		// Test no rememberme
		$wp_expiration = time() + apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user->get_id(), false );
		$wp_expire     = 0;
		do_action( 'set_auth_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), null );
		do_action( 'set_logged_in_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), 'logged_in' );
		wp_set_current_user( $user->get_id(), $user->get_login() );
		$cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertGreaterThanOrEqual( time() + ( 30 * MINUTE_IN_SECONDS ) - 30, $cookie_data->expiration );
		$this->assertLessThanOrEqual( time() + ( 30 * MINUTE_IN_SECONDS ) + 30, $cookie_data->expiration );
		wp_logout();

		// Test rememberme
		$wp_expiration = time() + apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user->get_id(), true );
		$wp_expire     = $wp_expiration + ( 12 * HOUR_IN_SECONDS );
		do_action( 'set_auth_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), null );
		do_action( 'set_logged_in_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), 'logged_in' );
		wp_set_current_user( $user->get_id(), $user->get_login() );
		$cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertGreaterThanOrEqual( time() + ( 30 * MINUTE_IN_SECONDS ) - 30, $cookie_data->expiration );
		$this->assertLessThanOrEqual( time() + ( 30 * MINUTE_IN_SECONDS ) + 30, $cookie_data->expiration );
		wp_logout();
	}

	public function test_editor_user(): void {
		$user = new user(
			$this->factory()->user->create_and_get(
				array(
					'role' => 'editor',
				)
			)
		);

		// Test no rememberme
		$wp_expiration = time() + apply_filters( 'auth_cookie_expiration', 2 * DAY_IN_SECONDS, $user->get_id(), false );
		$wp_expire     = 0;
		do_action( 'set_auth_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), null );
		do_action( 'set_logged_in_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), 'logged_in' );
		wp_set_current_user( $user->get_id(), $user->get_login() );
		$cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertGreaterThanOrEqual( time() + ( 2 * DAY_IN_SECONDS ) - 30, $cookie_data->expiration );
		$this->assertLessThanOrEqual( time() + ( 2 * DAY_IN_SECONDS ) + 30, $cookie_data->expiration );
		wp_logout();

		// Test rememberme
		$wp_expiration = time() + apply_filters( 'auth_cookie_expiration', 14 * DAY_IN_SECONDS, $user->get_id(), true );
		$wp_expire     = $wp_expiration + ( 12 * HOUR_IN_SECONDS );
		do_action( 'set_auth_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), null );
		do_action( 'set_logged_in_cookie', null, $wp_expire, $wp_expiration, $user->get_id(), 'logged_in' );
		wp_set_current_user( $user->get_id(), $user->get_login() );
		$cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertGreaterThanOrEqual( time() + ( 14 * DAY_IN_SECONDS ) - 30, $cookie_data->expiration );
		$this->assertLessThanOrEqual( time() + ( 14 * DAY_IN_SECONDS ) + 30, $cookie_data->expiration );
		wp_logout();
	}
}

require_once __DIR__ . '/cookiefaker.php';
