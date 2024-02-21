<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;

class UserCookies {

	protected const COOKIE_USER   = 'fdmg_labels_user';
	protected const COOKIE_EDITOR = 'fdmg_labels_editor';

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'set_user_cookie' ), 10, 4 );
		add_action( 'set_auth_cookie', array( $this, 'maybe_set_editor_cookie' ), 10, 4 );

		add_action( 'clear_auth_cookie', array( $this, 'clear_user_cookie' ) );
		add_action( 'clear_auth_cookie', array( $this, 'clear_editor_cookie' ) );

		/**
		 * The WordPress cookie expiration time is later than the actual
		 * session expiration time. Because of how the remember-me function
		 * in WordPress works, we need to set our cookie to the same time as
		 * the WordPress cookie expiration time. This can however result in our
		 * cookie existing for longer than the actual expiration time.
		 * This action will therefore if the WordPress session has expired,
		 * clear our cookies as well.
		 */
		add_action( 'auth_cookie_expired', array( $this, 'clear_cookies' ) );
	}

	/**
	 * Set a cookie with user data to be able to use its in JavaScript.
	 */
	public function set_user_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		$user = user::get( (int) $user_id );
		if ( ! $user ) {
			return;
		}

		$cookie_data = array(
			'id'           => $user_id,
			'display_name' => $user->get_display_name(),
		);
		$this->set_cookie( self::COOKIE_USER, wp_json_encode( $cookie_data ) ?: '', (int) $expire );
	}

	/**
	 * Set a cookie for editors to allow
	 */
	public function maybe_set_editor_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		if ( ! user_can( (int) $user_id, 'edit_posts' ) ) {
			return;
		}

		$this->set_cookie( self::COOKIE_EDITOR, '1', (int) $expire );
	}

	/**
	 * Set a cookie
	 *
	 * @param UserCookies::COOKIE_* $cookie_name
	 */
	protected function set_cookie( string $cookie_name, string $cookie_data, int $expires ): void {
		$cookiepath    = trim( (string) wp_parse_url( get_option( 'siteurl' ), PHP_URL_PATH ), '/' ) ?: '/'; // Path needs to be for the whole current subsite
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '/';

		$cookie_options = array(
			'expires'  => $expires,
			'path'     => $cookiepath,
			'domain'   => $cookie_domain,
			'secure'   => is_ssl(),
			'samesite' => 'None',
			'httponly' => false,
		);

		setcookie( $cookie_name, $cookie_data, $cookie_options );
	}

	public function clear_user_cookie(): void {
		$this->set_cookie( self::COOKIE_USER, '', time() - YEAR_IN_SECONDS );
	}

	public function clear_editor_cookie(): void {
		$this->set_cookie( self::COOKIE_EDITOR, '', time() - YEAR_IN_SECONDS );
	}
}
