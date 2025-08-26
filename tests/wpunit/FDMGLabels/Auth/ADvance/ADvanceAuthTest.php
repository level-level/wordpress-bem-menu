<?php

namespace FDMGLabels\Auth\ADvance;

use Clarkson\Theme\ACFPages\ADvance;
use FDMGLabels\Auth\IdentityProviders\ADvance\ADvanceIdentityProvider;
use FDMGLabels\CookieHelper;
use FDMGLabels\Tests\Helpers\Redirects;
use lucatume\WPBrowser\TestCase\WPTestCase;
use WPDieException;

class ADvanceAuthTest extends WPTestCase {

	public function setUp(): void {
		$this->cookie_helper    = new CookieHelper();
		$this->redirects_helper = new Redirects();
		update_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ), 'https://fdmg-labels.test' );
		parent::setUp();
	}

	public function tearDown(): void {
		delete_option( sprintf( 'options_%s_%s', ADvance::OPTION_GROUP, ADvance::OPTION_PAGES_BASE_URL ) );
		$_GET     = array();
		$_REQUEST = array();
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
			$this->assertStringContainsString( '/login', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'returl=http%3A%2F%2F', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( '%2Flogin%2Fafter-login', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'redirect_to%3Dhttps%253A%252F%252Fexample.com', $this->redirects_helper->get_redirect_location() );

			$this->assertNotEmpty( $this->cookie_helper->get_cookie( 'ADConCtrlCookie' ) );
		}
	}

	public function test_handle_logout_redirect() {
		$login = new ADvanceIdentityProvider( $this->cookie_helper );
		$this->redirects_helper->start();
		$this->cookie_helper->set_cookie( 'ADFEAuthCookie', 'somevalue', time() + 3600, true ); // tests if cookie gets cleared on logout.

		try {
			$login->handle_logout_page( 'https://example.com' );
			$this->fail( 'Expected WPDieException to be thrown' );
		} catch ( WPDieException $e ) {
			// Expected exception - continue with assertions
			$this->redirects_helper->stop();

			$this->redirects_helper->assert_redirected( true );
			$this->redirects_helper->assert_redirect_status( 302 );
			$this->assertStringContainsString( '/wp-login.php?action=logout', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( 'redirect_to=https%3A%2F%2Fexample.com', $this->redirects_helper->get_redirect_location() );
			$this->assertStringContainsString( '_wpnonce=', $this->redirects_helper->get_redirect_location() );

			$this->assertEmpty( $this->cookie_helper->get_cookie( 'ADFEAuthCookie' ) );
		}
	}
}
