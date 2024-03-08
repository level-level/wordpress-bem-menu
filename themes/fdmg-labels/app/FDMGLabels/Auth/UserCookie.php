<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;

class UserCookie {

	protected const COOKIE = 'fdmg_labels_user';

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
	 * Set a cookie with user data to be able to use its in JavaScript.
	 */
	public function maybe_set_cookie( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id, ?string $scheme, ?string $token = null ): void {
		$user = user::get( (int) $user_id );
		if ( ! $user instanceof user ) {
			return;
		}

		$cookie_data = $this->cookie_helper->build_cookie_data(
			(int) $user_id,
			array(
				'id'                   => (int) $user_id,
				'display_name'         => $user->get_display_name(),
				'fdmg_user_id'         => $user->get_fdmg_user_id(),
				'fdmg_organisation_id' => $user->get_fdmg_organisation_id(),
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

	public function clear_cookie(): void {
		$this->cookie_helper->clear_cookie( self::COOKIE, false );
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
		$user_id      = get_current_user_id();
		$cookie_valid = $this->cookie_helper->validate_cookie_data( $cookie_data, $user_id );
		if ( $cookie_valid ) {
			return;
		}
		$this->clear_cookie();
	}
}
