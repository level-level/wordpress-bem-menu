<?php

namespace FDMGLabels\Auth;

use FDMGLabels\CookieHelper;

class EditorCookie {

	protected const COOKIE = 'fdmg_labels_editor';

	protected CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'maybe_set_cookie' ), 10, 4 );
		add_action( 'clear_auth_cookie', array( $this, 'clear_cookie' ) );

		/**
		 * The WordPress cookie expiration time is later than the actual
		 * session expiration time. Because of how the remember-me function
		 * in WordPress works, we need to set our cookie to the same time as
		 * the WordPress cookie expiration time. This can however result in our
		 * cookie existing for longer than the actual expiration time.
		 * This action will therefore if the WordPress session has expired,
		 * clear our cookies as well.
		 */
		add_action( 'auth_cookie_expired', array( $this, 'clear_cookie' ) );
	}

	/**
	 * Determine if the user logging in is an editor. If so, set a cookie.
	 * This is used in the hosting configuration to determine if a page should
	 * be cached or not.
	 */
	public function maybe_set_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		if ( ! user_can( (int) $user_id, 'edit_posts' ) ) {
			return;
		}

		$this->cookie_helper->set_cookie(
			self::COOKIE,
			'1',
			(int) $expire,
			false
		);
	}

	/**
	 * Clear the editor cookie.
	 * This should be called both when the user logs out and when the WordPress
	 * user cookie has expired.
	 */
	public function clear_cookie(): void {
		$this->cookie_helper->clear_cookie( self::COOKIE, true );
	}
}
