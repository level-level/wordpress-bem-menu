<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;
use lucatume\WPBrowser\TestCase\WPTestCase;

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
		$administrator_user = new user(
			$this->factory()->user->create_and_get(
				array(
					'role'       => 'administrator',
					'meta_input' => array(
						'fdmg_user_id'         => '7945',
						'fdmg_organisation_id' => '7964',
					),
				)
			)
		);
		wp_set_auth_cookie( $administrator_user->get_id(), false, true );
		wp_set_current_user( $administrator_user->get_id(), $administrator_user->get_login() );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$administrator_cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertEquals( $administrator_user->get_id(), $administrator_cookie_data->data->id );
		$this->assertEquals( $administrator_user->get_display_name(), $administrator_cookie_data->data->display_name );
		$this->assertEquals( 7945, $administrator_cookie_data->data->fdmg_user_id );
		$this->assertEquals( 7964, $administrator_cookie_data->data->fdmg_organisation_id );

		wp_logout();

		// Assert no cookie is set
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );

		// Test a subscriber user
		$subscriber_user = new user(
			$this->factory()->user->create_and_get(
				array(
					'role'       => 'subscriber',
					'meta_input' => array(
						'fdmg_user_id'         => '4679',
						'fdmg_organisation_id' => '795',
					),
				)
			)
		);
		wp_set_auth_cookie( $subscriber_user->get_id(), false, true );
		wp_set_current_user( $subscriber_user->get_id(), $subscriber_user->get_login() );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );
		$subscriber_cookie_data = json_decode( stripslashes( $_COOKIE['fdmg_labels_user'] ) );
		$this->assertEquals( $subscriber_user->get_id(), $subscriber_cookie_data->data->id );
		$this->assertEquals( $subscriber_user->get_display_name(), $subscriber_cookie_data->data->display_name );
		$this->assertEquals( 4679, $subscriber_cookie_data->data->fdmg_user_id );
		$this->assertEquals( 795, $subscriber_cookie_data->data->fdmg_organisation_id );

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

		add_filter( 'auth_cookie_expiration', array( $this, 'change_cookie_expiration' ) );
		wp_set_auth_cookie( $user->ID, false, true );
		wp_set_current_user( $user->ID, $user->user_login );
		remove_filter( 'auth_cookie_expiration', array( $this, 'change_cookie_expiration' ) );

		// Assert cookie is set
		$this->assertArrayHasKey( 'fdmg_labels_user', $_COOKIE );

		// Assert cookie is deleted
		$user_cookie = new UserCookie( new AuthCookieHelper() );
		$user_cookie->clear_cookie_if_invalid();
		$this->assertArrayNotHasKey( 'fdmg_labels_user', $_COOKIE );

		wp_logout();
	}

	public function change_cookie_expiration(): int {
		return -1;
	}
}

require_once __DIR__ . '/cookiefaker.php';
