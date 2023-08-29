<?php

namespace FDMGLabels\Auth;

use Clarkson_Core\WordPress_Object\user;

class FDMGIdentification {

	public const META_FDMG_USER_ID         = 'fdmg_user_id';
	public const META_FDMG_ORGANISATION_ID = 'fdmg_organisation_id';

	protected const COOKIE_USER         = 'fdmg_user_id';
	protected const COOKIE_ORGANISATION = 'fdmg_organisation_id';

	public function register_hooks(): void {
		add_action( 'set_auth_cookie', array( $this, 'set_cookies' ), 10, 4 );
		add_action( 'clear_auth_cookie', array( $this, 'clear_cookies' ) );
	}

	/**
	 * Set cookies used for server side tracking
	 */
	public function set_cookies( ?string $logged_in_cookie, ?int $expire, ?int $expiration, ?int $user_id ): void {
		$user = user::get( (int) $user_id );
		if ( ! $user instanceof user ) {
			return;
		}

		$this->set_cookie( self::COOKIE_USER, (string) $user->get_fdmg_user_id(), (int) ( $expire ?: $expiration ) );
		$this->set_cookie( self::COOKIE_ORGANISATION, (string) $user->get_fdmg_organisation_id(), (int) ( $expire ?: $expiration ) );
	}

	/**
	 * Set a cookie
	 *
	 * @param FDMGIdentification::COOKIE_* $cookie_name
	 */
	protected function set_cookie( string $cookie_name, string $cookie_data, int $expires ): void {
		$cookiepath     = defined( 'COOKIEPATH' ) ? COOKIEPATH : '/';
		$sitecookiepath = defined( 'SITECOOKIEPATH' ) ? SITECOOKIEPATH : '/';
		$cookie_domain  = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '/';

		$cookie_options = array(
			'expires'  => $expires,
			'path'     => $cookiepath,
			'domain'   => $cookie_domain,
			'secure'   => is_ssl(),
			'samesite' => 'None',
			'httponly' => false,
		);

		setcookie( $cookie_name, $cookie_data, $cookie_options );

		if ( $cookiepath !== $sitecookiepath ) {
			$cookie_options['path'] = $sitecookiepath;
			setcookie( $cookie_name, $cookie_data, $cookie_options );
		}
	}

	public function clear_cookies(): void {
		$this->set_cookie( self::COOKIE_USER, '', time() - YEAR_IN_SECONDS );
		$this->set_cookie( self::COOKIE_ORGANISATION, '', time() - YEAR_IN_SECONDS );
	}

}
