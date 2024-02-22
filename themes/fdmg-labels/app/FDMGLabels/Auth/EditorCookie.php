<?php

namespace FDMGLabels\Auth;

class EditorCookie {

	protected const COOKIE = 'fdmg_labels_editor';

	protected AuthCookieHelper $cookie_helper;

	public function __construct( AuthCookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'maybe_set_cookie' ), 10, 6 );
		add_action( 'clear_auth_cookie', array( $this, 'clear_cookie' ) );
		add_action( 'init', array( $this, 'clear_cookie_if_invalid' ) );
	}

	/**
	 * Determine if the user logging in is an editor. If so, set a cookie.
	 * This is used in the hosting configuration to determine if a page should
	 * be cached or not.
	 */
	public function maybe_set_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id, ?string $scheme, ?string $token = null ): void {
		if ( ! user_can( (int) $user_id, 'edit_posts' ) ) {
			return;
		}

		$cookie_data = $this->cookie_helper->build_cookie_data(
			(int) $user_id,
			array(
				'is_editor' => true,
			),
			(int) $expiration,
			(string) $token
		);

		$this->cookie_helper->set_cookie(
			self::COOKIE,
			$cookie_data,
			(int) $expire,
			false
		);
	}

	/**
	 * Clear the cookie.
	 */
	public function clear_cookie(): void {
		$this->cookie_helper->clear_cookie( self::COOKIE, true );
	}

	/**
	 * Clear the cookie if it's invalid.
	 */
	public function clear_cookie_if_invalid(): void {
		// If cookie is not set, there is no need to clear it.
		$cookie_data = $this->cookie_helper->get_cookie( self::COOKIE );
		if ( $cookie_data === null ) {
			return;
		}

		// If cookie is valid, there is no need to clear it.
		$cookie_valid = $this->cookie_helper->validate_cookie_data( $cookie_data, get_current_user_id() );
		if ( $cookie_valid ) {
			return;
		}
		$this->clear_cookie();
	}
}
