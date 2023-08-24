<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;

class FDMGIdentificationTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->fdmg_identification = new FDMGIdentification();
	}

	public function tearDown(): void {
		parent::tearDown();
	}

	public function test_set_cookies(): void {
		// Create an administrator user and a subscriber user
		$administrator_user = $this->factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		wp_set_auth_cookie( $administrator_user->ID, false, true );
		wp_set_current_user( $administrator_user->ID, $administrator_user->user_login );
		// Assert cookies!
		wp_logout();
		// Assert no cookies!

		$subscriber_user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		wp_set_auth_cookie( $subscriber_user->ID, false, true );
		wp_set_current_user( $subscriber_user->ID, $subscriber_user->user_login );
		// Assert cookies!
		wp_logout();
		// Assert no cookies!
	}
}

/**
 * Overwrite php setcookie function in this namespace
 */
function setcookie( string $name, string $value, array $data ): void {
	if ( $data['expires'] < time() ) {
		unset( $_COOKIE[ $name ] );
	} else {
		$_COOKIE[ $name ] = $value;
	}
}
