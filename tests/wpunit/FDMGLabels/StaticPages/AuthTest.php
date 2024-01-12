<?php

namespace FDMGLabels\StaticPages;

use Codeception\TestCase\WPTestCase;
use FDMGLabels\Tests\Helpers\Redirects;

class AuthTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->redirects_helper = new Redirects();
	}

	public function test_login(): void {
		add_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );

		$controller = new LoginController();
		$this->redirects_helper->start();
		$controller->handle_request( '' );
		$this->redirects_helper->stop();

		$this->redirects_helper->assert_redirected( true );
		$this->redirects_helper->assert_redirect_status( 302 );
		$this->assertStringContainsString( 'https://example-login-url.test', $this->redirects_helper->get_redirect_location() );

		remove_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
	}

	public function test_logout(): void {
		add_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );

		$_REQUEST['redirect_to'] = 'https://example-after-logout-redirect.test';
		$controller              = new LogoutController();
		$this->redirects_helper->start();
		$controller->handle_request( '' );
		$this->redirects_helper->stop();

		$this->redirects_helper->assert_redirected( true );
		$this->redirects_helper->assert_redirect_location( wp_specialchars_decode( wp_logout_url( 'https://example-after-logout-redirect.test' ) ) );
		$this->redirects_helper->assert_redirect_status( 302 );

		remove_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
	}

	public function change_openid_login_url( string $url ): string {
		$url    = explode( '?', $url );
		$url[0] = 'https://example-login-url.test';
		return implode( '?', $url );
	}
}
