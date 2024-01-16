<?php

namespace FDMGLabels\StaticPages;

use Codeception\TestCase\WPTestCase;
use FDMGLabels\Tests\Helpers\Redirects;

class AuthTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->redirects_helper = new Redirects();
	}

	public function tearDown(): void {
		$_GET = array();
		$_REQUEST = array();
		parent::tearDown();
	}

	public function test_login(): void {
		add_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );

		$_GET['ref']         = '1234';
		$_GET['kc_idp_hint'] = 'someone@example.test';
		$_GET['_ga']         = '5678';
		$_GET['loginHint']   = 'anotherone@example.test';
		$controller          = new LoginController();
		$this->redirects_helper->start();
		$controller->handle_request( '' );
		$this->redirects_helper->stop();

		$this->redirects_helper->assert_redirected( true );
		$this->redirects_helper->assert_redirect_status( 302 );
		$this->assertStringContainsString( 'https://example-login-url.test', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'ref=1234', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'kc_idp_hint=someone%40example.test', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( '_ga=5678', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'loginHint=anotherone%40example.test', $this->redirects_helper->get_redirect_location() );

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
