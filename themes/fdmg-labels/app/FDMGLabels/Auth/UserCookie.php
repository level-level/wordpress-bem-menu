<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;
use FDMGLabels\CookieHelper;

class UserCookie {

	protected const COOKIE = 'fdmg_labels_user';

	protected CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'set_cookie' ), 10, 4 );
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
	 * Set a cookie with user data to be able to use its in JavaScript.
	 */
	public function set_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		$user = user::get( (int) $user_id );
		if ( ! $user ) {
			return;
		}

		$cookie_data = array(
			'id'           => $user_id,
			'display_name' => $user->get_display_name(),
		);
		$this->cookie_helper->set_cookie(
			self::COOKIE,
			wp_json_encode( $cookie_data ) ?: '',
			(int) $expire,
			false
		);
	}

	public function clear_cookie(): void {
		$this->cookie_helper->clear_cookie( self::COOKIE, false );
	}
}
