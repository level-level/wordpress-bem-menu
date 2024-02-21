<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;
use FDMGLabels\CookieHelper;

class FDMGIdentification {

	public const META_FDMG_USER_ID         = 'fdmg_user_id';
	public const META_FDMG_ORGANISATION_ID = 'fdmg_organisation_id';

	protected const COOKIE_USER         = 'fdmg_user_id';
	protected const COOKIE_ORGANISATION = 'fdmg_organisation_id';

	protected CookieHelper $cookie_helper;

	public function __construct( CookieHelper $cookie_helper ) {
		$this->cookie_helper = $cookie_helper;
	}

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'set_cookies' ), 10, 4 );

		add_action( 'clear_auth_cookie', array( $this, 'clear_cookies' ) );

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
	 * Set cookies used for server side tracking
	 */
	public function set_cookies( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		$user = user::get( (int) $user_id );
		if ( ! $user instanceof user ) {
			return;
		}

		$this->cookie_helper->set_cookie( self::COOKIE_USER, (string) $user->get_fdmg_user_id(), (int) $expire, false );
		$this->cookie_helper->set_cookie( self::COOKIE_ORGANISATION, (string) $user->get_fdmg_organisation_id(), (int) $expire, false );
	}

	public function clear_cookies(): void {
		$this->cookie_helper->clear_cookie( self::COOKIE_USER, false );
		$this->cookie_helper->clear_cookie( self::COOKIE_ORGANISATION, false );
	}
}
