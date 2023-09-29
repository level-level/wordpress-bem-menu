<?php

namespace FDMGLabels\Auth;

use Codeception\TestCase\WPTestCase;
use OpenID_Connect_Generic;
use WP_User;

class OpenIDConnectTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->openid_user_claim = array(
			'organizationId'   => '3703147',
			'lastName'         => 'de Level',
			'firstName'        => 'L.',
			'realmRoles'       => '[offline_access, default-roles-fd, uma_authorization]',
			'email_verified'   => true,
			'roles'            => array(
				'manage-account',
				'manage-account-links',
				'view-profile',
			),
			'fullName'         => 'L. de Level',
			'customerNumber'   => '4601292',
			'email'            => 'levellevel@asdasd.nl',
			'allSubscriptions' => '',
			'sapId'            => '4601292',
		);
	}

	public function test_create_user(): void {
		// Because we cannot modify the settings before the unit test, we temporary add the preferred_username to the claim
		$claim                       = $this->openid_user_claim;
		$claim['preferred_username'] = 'TEMP_PREFERRED_USERNAME';

		$user = OpenID_Connect_Generic::instance()->client_wrapper->create_new_user( $claim['sapId'], $claim );
		$this->assertInstanceOf( WP_User::class, $user );
		$this->assertEquals( '4601292-levellevel', $user->user_login );
		$this->assertEquals( 'levellevel@asdasd.nl', $user->user_email );
		$this->assertEquals( 'L.', $user->first_name );
		$this->assertEquals( 'de Level', $user->last_name );
		$this->assertEquals( 'L. de Level', $user->display_name );
		$this->assertEquals( array( 'fdmg_account_holder' ), $user->roles );
		$this->assertEquals( '4601292', get_user_meta( $user->ID, 'fdmg_user_id', true ) );
		$this->assertEquals( '3703147', get_user_meta( $user->ID, 'fdmg_organisation_id', true ) );
	}

	public function test_update_user(): void {
		$user = $this->factory()->user->create_and_get(
			array(
				'role'       => 'administrator',
				'user_email' => 'someadmin@example.test',
				'user_pass'  => wp_generate_password(),
				'user_login' => 'someadmin',
			)
		);

		do_action( 'openid-connect-generic-update-user-using-current-claim', $user, $this->openid_user_claim ); // phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores

		$user = get_user_by( 'id', $user->ID );
		$this->assertInstanceOf( WP_User::class, $user );
		$this->assertEquals( 'someadmin', $user->user_login );
		$this->assertEquals( 'someadmin@example.test', $user->user_email );
		$this->assertEquals( 'L.', $user->first_name );
		$this->assertEquals( 'de Level', $user->last_name );
		$this->assertEquals( 'L. de Level', $user->display_name );
		$this->assertEquals( array( 'administrator', 'fdmg_account_holder' ), $user->roles );
		$this->assertEquals( '4601292', get_user_meta( $user->ID, 'fdmg_user_id', true ) );
		$this->assertEquals( '3703147', get_user_meta( $user->ID, 'fdmg_organisation_id', true ) );
	}

	public function test_set_account_holder_user_roles(): void {
		$user = $this->factory()->user->create_and_get( array( 'role' => 'fdmg_account_holder' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'fdmg_account_holder' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'subscriber', 'fdmg_account_holder' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'administrator', 'fdmg_account_holder' ), $user->roles );
	}

	public function test_set_subscriber_user_roles(): void {
		// Add viewer role to claim
		$claim            = $this->openid_user_claim;
		$claim['roles'][] = 'VIEWER';

		$user = $this->factory()->user->create_and_get( array( 'role' => 'fdmg_account_holder' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'fdmg_subscriber' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'subscriber', 'fdmg_subscriber' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		( new OpenIDConnect() )->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'administrator', 'fdmg_subscriber' ), $user->roles );
	}
}
