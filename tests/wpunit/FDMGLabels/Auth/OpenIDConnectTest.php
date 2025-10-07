<?php

namespace FDMGLabels\Auth;

use FDMGLabels\Auth\IdentityProviders\KeyCloak\OpenIDConnectHooks;
use FDMGLabels\CookieHelper;
use lucatume\WPBrowser\TestCase\WPTestCase;
use OpenID_Connect_Generic;
use WP_Session_Tokens;
use WP_User;

class OpenIDConnectTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$_COOKIE = array();

		$this->subject_identity  = '599a88ba-0651-4370-ab1a-976ed3fa7ad7';
		$this->token_response    = array(
			'access_token'       => 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICItSXM4LU51WkZvQTVfXzBZT0tUaDFWSDJsSEcyc3dEQ1RRYnpjS1RXRXg4In0.eyJleHAiOjE3MDkwMzEzNzMsImlhdCI6MTcwOTAzMDUzMywiYXV0aF90aW1lIjoxNzA5MDMwNTMyLCJqdGkiOiJhMzNhNWJmYS01YThiLTQ0ZGYtYjA2NS1jZTMwYWZhY2E2OTEiLCJpc3MiOiJodHRwczovL2xvZ2luLWRldi5mZG1nLm5sL2F1dGgvcmVhbG1zL2ZkIiwic3ViIjoiMTIzZGI4ZGYtNjUzZC00YmZlLTk2ZWQtYjI0MjU3ODZkN2FjIiwidHlwIjoiQmVhcmVyIiwiYXpwIjoid29yZHByZXNzX2VuZXJnZWlhIiwic2Vzc2lvbl9zdGF0ZSI6IjkxYTZhN2RkLTYzMGItNGRlNy04MWExLTcyYWQwOGE0NjkwYSIsImFsbG93ZWQtb3JpZ2lucyI6WyJodHRwOi8vKmltcGFjdC1pbnZlc3Rvci52YWxldCIsImh0dHBzOi8vZW5lcmdlaWEubG9jYWwiLCJodHRwOi8vZW5lcmdlaWEubG9jYWwiLCJodHRwczovL2xvY2FsaG9zdDoqIiwiaHR0cHM6Ly8qaW1wYWN0LWludmVzdG9yLmxvY2FsIiwiaHR0cDovL2VuZXJnZWlhLnRlc3QiLCJodHRwczovLyppbXBhY3QtaW52ZXN0b3IudGVzdCIsImh0dHBzOi8vKi5sb2NhbCIsImh0dHBzOi8vZW5lcmdlaWEtYWNjLmxldmVsLWxldmVsLm5sIiwiaHR0cDovL3BlbnNpb2VuLXByby52YWxldCIsImh0dHA6Ly9lbmVyZ2VpYS52YWxldCIsImh0dHBzOi8vZW5lcmdlaWEtdGVzdC5sZXZlbC1sZXZlbC5ubCIsImh0dHA6Ly8qaW1wYWN0LWludmVzdG9yLnRlc3QiLCJodHRwczovL2VuZXJnZWlhLnZhbGV0IiwiaHR0cHM6Ly9lbmVyZ2VpYS50ZXN0IiwiaHR0cDovLyppbXBhY3QtaW52ZXN0b3IubG9jYWwiLCJodHRwOi8vZW5lcmdlaWEtdGVzdC5sZXZlbC1sZXZlbC5ubCIsImh0dHBzOi8vaW1wYWN0LWludmVzdG9yLnRlc3QiLCJodHRwOi8vKi50ZXN0IiwiaHR0cDovL2ltcGFjdC1pbnZlc3Rvci50ZXN0IiwiaHR0cDovLyoubG9jYWwiLCJodHRwczovLyoudGVzdCIsImh0dHA6Ly9pbXBhY3QtaW52ZXN0b3IubG9jYWwiLCJodHRwczovLyoudmFsZXQiLCJodHRwczovLyppbXBhY3QtaW52ZXN0b3IudmFsZXQiLCJodHRwczovL2ltcGFjdC1pbnZlc3Rvci10ZXN0LmxldmVsLWxldmVsLm5sIiwiaHR0cDovL2xvY2FsaG9zdDoqIiwiaHR0cDovLyoudmFsZXQiLCJodHRwczovL3BlbnNpb2VuLXByby52YWxldCIsImh0dHBzOi8vaW1wYWN0LWludmVzdG9yLmxvY2FsIl0sInNjb3BlIjoib2ZmbGluZV9hY2Nlc3Mgd29yZHByZXNzX3VzZXIgb3BlbmlkIGVtYWlsIHByb3BhZ2F0ZV90b2tlbiIsInNpZCI6IjkxYTZhN2RkLTYzMGItNGRlNy04MWExLTcyYWQwOGE0NjkwYSIsImxhc3ROYW1lIjoiZGUgTGV2ZWwiLCJmaXJzdE5hbWUiOiJMLiIsInJlYWxtUm9sZXMiOiJbb2ZmbGluZV9hY2Nlc3NdIiwiZW1haWxfdmVyaWZpZWQiOnRydWUsImZ1bGxOYW1lIjoiTC4gZGUgTGV2ZWwiLCJyZW1lbWJlck1lIjp0cnVlLCJjdXN0b21lck51bWJlciI6IjQ2MDEyOTIiLCJlbWFpbCI6ImxldmVsbGV2ZWxAYXNkYXNkLm5sIiwiYWxsU3Vic2NyaXB0aW9ucyI6IiIsInNhcElkIjoiNDYwMTI5MiJ9.pEgIKIW6jxNO9fODqxPIjF3P-FtZp6RMtnk5Mp1vl3B2P3nYPqKXGfKLeIk8BP7ph8sfn_-mN-g7bF2-4qf_IAFlPgRK13HVtlQPobeRCH_3VBvD2XV3ytmFVSrzyJux49EPveBl8kG3DCyYS-R8_qqZfjDFUI-sP2GOJ2QIKd7eYFmDPC9Fp19IdRJytiyk3h_8o_DVs25WUNgz933h5B5G9EP7RoMs-6KSY-B5AF5ONNAbWlL_3CFNVvf3DPUe37_fOJe9EbZkb3rykCF1rd3PAs4SagcUsypLi3Sw4XIHj8nembbMobpGv9GGVBwkC_DpanGZtgU1XkLNIAZeSA',
			'expires_in'         => 840,
			'refresh_expires_in' => 2591999,
			'refresh_token'      => 'eyJhbGciOiJIUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICI2NzViZTFlNi05NWY5LTRlMTMtOTk1Ny04MTg3M2M5YzU1OGEifQ.eyJleHAiOjE3MTE2MjI1MzIsImlhdCI6MTcwOTAzMDUzMywianRpIjoiYjkwMmVhYWUtNGNkYS00MTYyLTk4NTUtNWU4ODViODYwMWExIiwiaXNzIjoiaHR0cHM6Ly9sb2dpbi1kZXYuZmRtZy5ubC9hdXRoL3JlYWxtcy9mZCIsImF1ZCI6Imh0dHBzOi8vbG9naW4tZGV2LmZkbWcubmwvYXV0aC9yZWFsbXMvZmQiLCJzdWIiOiIxMjNkYjhkZi02NTNkLTRiZmUtOTZlZC1iMjQyNTc4NmQ3YWMiLCJ0eXAiOiJPZmZsaW5lIiwiYXpwIjoid29yZHByZXNzX2VuZXJnZWlhIiwic2Vzc2lvbl9zdGF0ZSI6IjkxYTZhN2RkLTYzMGItNGRlNy04MWExLTcyYWQwOGE0NjkwYSIsInNjb3BlIjoib2ZmbGluZV9hY2Nlc3Mgd29yZHByZXNzX3VzZXIgb3BlbmlkIGVtYWlsIHByb3BhZ2F0ZV90b2tlbiIsInNpZCI6IjkxYTZhN2RkLTYzMGItNGRlNy04MWExLTcyYWQwOGE0NjkwYSJ9.-QGIkmRXthcjDit4T1tbkefKSmpIy3ThM5-SEelcCJk',
			'token_type'         => 'Bearer',
			'id_token'           => 'eyJhbGciOiJSUzI1NiIsInR5cCIgOiAiSldUIiwia2lkIiA6ICItSXM4LU51WkZvQTVfXzBZT0tUaDFWSDJsSEcyc3dEQ1RRYnpjS1RXRXg4In0.eyJleHAiOjE3MDkwMzEzNzMsImlhdCI6MTcwOTAzMDUzMywiYXV0aF90aW1lIjoxNzA5MDMwNTMyLCJqdGkiOiJkOWQ2NTUzNC1jM2ViLTQ2ZDAtYjdkNC0wZTA5NGI0YjU5YzIiLCJpc3MiOiJodHRwczovL2xvZ2luLWRldi5mZG1nLm5sL2F1dGgvcmVhbG1zL2ZkIiwiYXVkIjoid29yZHByZXNzX2VuZXJnZWlhIiwic3ViIjoiMTIzZGI4ZGYtNjUzZC00YmZlLTk2ZWQtYjI0MjU3ODZkN2FjIiwidHlwIjoiSUQiLCJhenAiOiJ3b3JkcHJlc3NfZW5lcmdlaWEiLCJzZXNzaW9uX3N0YXRlIjoiOTFhNmE3ZGQtNjMwYi00ZGU3LTgxYTEtNzJhZDA4YTQ2OTBhIiwiYXRfaGFzaCI6IjY0UWtzOTY2VzZYQUk4LV9OeVJ5V3ciLCJzaWQiOiI5MWE2YTdkZC02MzBiLTRkZTctODFhMS03MmFkMDhhNDY5MGEiLCJsYXN0TmFtZSI6ImRlIExldmVsIiwiZmlyc3ROYW1lIjoiTC4iLCJyZWFsbVJvbGVzIjoiW29mZmxpbmVfYWNjZXNzXSIsImVtYWlsX3ZlcmlmaWVkIjp0cnVlLCJmdWxsTmFtZSI6IkwuIGRlIExldmVsIiwiY3VzdG9tZXJOdW1iZXIiOiI0NjAxMjkyIiwiZW1haWwiOiJsZXZlbGxldmVsQGFzZGFzZC5ubCIsImFsbFN1YnNjcmlwdGlvbnMiOiIiLCJzYXBJZCI6IjQ2MDEyOTIifQ.K7YnLOHq34flfVlzyor3donJVr9AteFPCYNyj_ur7_NQUAQDzu9AZKy60-akqtSRNpnCsTWhLxhtegJMC1wJnt2zPM4XJ-312_61DblIHXe4dudjj0xVZCQv_JLtqp5BAmCMpobNex_2DXerKMZROe6VyE_3ImdN_4Pgirl7Nh1G68MLGKaosoZz3S9fy3HygABwwLfaOoitJzv2CdkOtEzaMNE4316xPvQAp2DnuDnHTXntpuVCqLFhUmplZQOpA2fhWg9-900fWy4UKhWyu6eqHAtbJ0jVYveT7wE2hb4PzKw9r-KCqOGr9gJ0yZZUgyT2ZqljyxtWCF9qXRqgGQ',
			'not-before-policy'  => 1606906584,
			'session_state'      => '91a6a7dd-630b-4de7-81a1-72ad08a4690a',
			'scope'              => 'offline_access wordpress_user openid email propagate_token',
		);
		$this->id_token_claim    = array(
			'exp'              => 1756811321,
			'iat'              => 1756810721,
			'auth_time'        => 1756810721,
			'jti'              => 'ef60c1a2-4dac-40d2-b926-643d8b3b79e3',
			'iss'              => 'https://login-dev.fdmg.nl/auth/realms/fd',
			'aud'              => 'wordpress_energeia',
			'sub'              => '599a88ba-0651-4370-ab1a-976ed3fa7ad7',
			'typ'              => 'ID',
			'azp'              => 'wordpress_energeia',
			'sid'              => '9bf25f44-e802-4fb3-86aa-6136f1362ccf',
			'at_hash'          => 'KH9NmGz5-lZ8Oyd_mlq7KQ',
			'organizationId'   => '3703147',
			'lastName'         => 'de Level',
			'email_verified'   => true,
			'roles'            => array(
				'VIEWER',
			),
			'fullName'         => 'L. de Level',
			'rememberMe'       => true,
			'customerNumber'   => '4601292',
			'email'            => 'levellevel@asdasd.nl',
			'allSubscriptions' => '',
			'sapId'            => '4601292',
		);
		$this->openid_user_claim = array(
			'organizationId'   => '3703147',
			'lastName'         => 'de Level',
			'firstName'        => 'L.',
			'realmRoles'       => '[default-roles-fd, uma_authorization]',
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

	public function tearDown(): void {
		parent::tearDown();
		$_COOKIE = array();
	}

	public function test_create_user(): void {
		// Because we cannot modify the settings before the unit test, we temporary add the preferred_username to the claim
		$claim                       = $this->openid_user_claim;
		$claim['preferred_username'] = 'TEMP_PREFERRED_USERNAME';

		$user = OpenID_Connect_Generic::instance()->client_wrapper->create_new_user( $claim['sapId'], $claim );
		$this->assertInstanceOf( WP_User::class, $user );
		$this->assertEquals( 'customer-4601292', $user->user_login );
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
		$openid_connect_hooks = new OpenIDConnectHooks( new CookieHelper() );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'fdmg_account_holder' ) );
		$openid_connect_hooks->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'fdmg_account_holder' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		$openid_connect_hooks->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'subscriber', 'fdmg_account_holder' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		$openid_connect_hooks->set_user_roles( $user, $this->openid_user_claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'administrator', 'fdmg_account_holder' ), $user->roles );
	}

	public function test_set_subscriber_user_roles(): void {
		// Add viewer role to claim
		$claim            = $this->openid_user_claim;
		$claim['roles'][] = 'VIEWER';

		$openid_connect_hooks = new OpenIDConnectHooks( new CookieHelper() );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'fdmg_account_holder' ) );
		$openid_connect_hooks->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'fdmg_subscriber' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'subscriber' ) );
		$openid_connect_hooks->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'subscriber', 'fdmg_subscriber' ), $user->roles );

		$user = $this->factory()->user->create_and_get( array( 'role' => 'administrator' ) );
		$openid_connect_hooks->set_user_roles( $user, $claim );
		$user = get_user_by( 'id', $user->ID );
		$this->assertEquals( array( 'administrator', 'fdmg_subscriber' ), $user->roles );
	}

	public function test_remember_me_cookie_expiration(): void {
		// Create user
		$user = $this->factory()->user->create_and_get( array( 'role' => 'fdmg_account_holder' ) );

		// Set openID data in meta
		update_user_meta( $user->ID, 'openid-connect-generic-last-token-response', $this->token_response );
		update_user_meta( $user->ID, 'openid-connect-generic-last-id-token-claim', $this->id_token_claim );
		update_user_meta( $user->ID, 'openid-connect-generic-last-user-claim', $this->openid_user_claim );

		// Do OpenID Connect login logic
		OpenID_Connect_Generic::instance()->client_wrapper->login_user( $user, $this->token_response, $this->id_token_claim, $this->openid_user_claim, $this->subject_identity );

		// Validate session expiration
		$manager                = WP_Session_Tokens::get_instance( $user->ID );
		$all_sessions           = $manager->get_all();
		$correct_expiration     = time() + $this->token_response['refresh_expires_in'];
		$correct_expiration_set = false;
		foreach ( $all_sessions as $session ) {
			if ( abs( $session['expiration'] - $correct_expiration ) <= 5 ) {
				$correct_expiration_set = true;
				break;
			}
		}
		$this->assertTrue( $correct_expiration_set, 'The remember me expiration was not set in the user session.' );

		// Cleanup
		wp_logout();
	}
}

require_once __DIR__ . '/cookiefaker.php';
