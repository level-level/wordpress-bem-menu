<?php

namespace FDMGLabels\Auth\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Auth\IdentityProviders\ADvance\ADvanceIdentityProvider;
use FDMGLabels\CookieHelper;
use FDMGLabels\StaticPages\LoginController;
use FDMGLabels\Tests\Helpers\Redirects;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPDieException;

class HandleLoginTest extends WPTestCase {

	public function setUp(): void {
		$this->cookie_helper = new CookieHelper();
		$this->redirects_helper = new Redirects();
		$this->Login_controller = new LoginController();
		update_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ), 'https://fdmg-labels.test' );
		parent::setUp();
	}

	public function tearDown(): void {

		parent::tearDown();
	}

	public function test_handle_login_redirect() {
		$login = new ADvanceIdentityProvider( $this->cookie_helper );
		$this->redirects_helper->start();

		try {
			$login->handle_login_page( 'https://example.com' );
			$this->fail( 'Expected WPDieException to be thrown' );
		} catch ( WPDieException $e ) {
			// Expected exception - continue with assertions
			$this->redirects_helper->stop();

			$this->redirects_helper->assert_redirected( true );
			$this->redirects_helper->assert_redirect_status( 302 );
			$this->assertStringContainsString( 'https://fdmg-labels.test', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( '/login', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'returl=http%3A%2F%2Ffdmg-labels.test%2Flogin%2Fafter-login', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'redirect_to%3Dhttps%253A%252F%252Fexample.com', $this->redirects_helper->get_redirect_location() );

			$this->assertNotEmpty( $this->cookie_helper->get_cookie( 'ADConCtrlCookie' ) );
		}
	}

	public function test_handle_after_login_redirect() {
		$login = new ADvanceIdentityProvider( $this->cookie_helper );
		$this->redirects_helper->start();
		$this->cookie_helper->set_cookie( 'ADFEAuthCookie', 'test_value', time() + HOUR_IN_SECONDS, true ); // to make sure we get the correct call from the api to redirect us to the correct place.
		$this->cookie_helper->set_cookie( 'ADConCtrlCookie', 'test_value', time() + HOUR_IN_SECONDS, true ); // to make sure we get the correct call from the api to redirect us to the correct place.

		try {
			$login->handle_after_login_page( 'https://example.com' );
			$this->fail( 'Expected WPDieException to be thrown' );
		} catch ( WPDieException $e ) {
			// Expected exception - continue with assertions
			$this->redirects_helper->stop();

			$this->redirects_helper->assert_redirected( true );
			$this->redirects_helper->assert_redirect_status( 302 );

			$this->assertStringContainsString( 'https://fdmg-labels.test', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( '/login', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( '%2FLogin%2FAutoLogin%2F1%2F', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'redirect_to%3Dhttps%253A%252F%252Fexample.com', $this->redirects_helper->get_redirect_location() );
		}
	}
}
