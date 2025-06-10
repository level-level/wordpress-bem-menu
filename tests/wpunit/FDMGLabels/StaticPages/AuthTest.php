<?php

namespace FDMGLabels\StaticPages;

use FDMGLabels\Tests\Helpers\Redirects;
use lucatume\WPBrowser\TestCase\WPTestCase;

class AuthTest extends WPTestCase {

	public function setUp(): void {
		parent::setUp();

		$this->redirects_helper = new Redirects();
	}

	public function tearDown(): void {
		$_GET     = array();
		$_REQUEST = array();
		parent::tearDown();
	}

	public function test_login(): void {
		add_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
		add_filter( 'shortcode_atts_openid_connect_generic_auth_url', array( $this, 'change_openid_scope' ) );

		$_GET['ref']         = '1234';
		$_GET['kc_idp_hint'] = 'MicrosoftAuth';
		$_GET['_ga']         = '5678';
		$_GET['loginHint']   = 'anotherone@example.test';
		$_GET['method']      = 'ipBasedRedirect';
		$controller          = new LoginController();
		$this->redirects_helper->start();
		$controller->handle_request( '' );
		$this->redirects_helper->stop();

		$this->redirects_helper->assert_redirected( true );
		$this->redirects_helper->assert_redirect_status( 302 );
		$this->assertStringContainsString( 'https://example-login-url.test', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'ref=1234', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'kc_idp_hint=MicrosoftAuth', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( '_ga=5678', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'loginHint=anotherone%40example.test', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'method=ipBasedRedirect', $this->redirects_helper->get_redirect_location() );
		$this->assertStringContainsString( 'scope=openid%20profile%20email', $this->redirects_helper->get_redirect_location(), 'Parameters must be rawurlencoded, not urlencoded' );

		remove_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
		remove_filter( 'shortcode_atts_openid_connect_generic_auth_url', array( $this, 'change_openid_scope' ) );
	}

	public function test_logout(): void {
		add_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
		add_filter( 'shortcode_atts_openid_connect_generic_auth_url', array( $this, 'change_openid_scope' ) );

		$_REQUEST['redirect_to'] = 'https://example-after-logout-redirect.test';
		$controller              = new LogoutController();
		$this->redirects_helper->start();
		$controller->handle_request( '' );
		$this->redirects_helper->stop();

		$this->redirects_helper->assert_redirected( true );
		$this->redirects_helper->assert_redirect_location( wp_specialchars_decode( wp_logout_url( 'https://example-after-logout-redirect.test' ) ) );
		$this->redirects_helper->assert_redirect_status( 302 );

		remove_filter( 'openid-connect-generic-auth-url', array( $this, 'change_openid_login_url' ) );
		remove_filter( 'shortcode_atts_openid_connect_generic_auth_url', array( $this, 'change_openid_scope' ) );
	}

	public function change_openid_login_url( string $url ): string {
		$url    = explode( '?', $url );
		$url[0] = 'https://example-login-url.test';
		return implode( '?', $url );
	}

	public function change_openid_scope( array $atts ): array {
		$atts['scope'] = 'openid profile email';
		return $atts;
	}
}
