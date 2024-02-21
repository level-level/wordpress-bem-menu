<?php
namespace FDMGLabels;

class CookieHelper {

	/**
	 * Sets a cookie using the PHP setcookie function.
	 *
	 * @param string $cookie_name The name of the cookie
	 * @param string $cookie_data The data to store in the cookie
	 * @param integer $expires The timestamp the cookie expires
	 * @param boolean $http_only Whether the cookie should be HTTP only, which means it can't be accessed by JavaScript
	 * @return boolean
	 */
	public function set_cookie( string $cookie_name, string $cookie_data, int $expires, bool $http_only = true ): bool {
		$cookiepath    = trim( (string) wp_parse_url( get_option( 'siteurl' ), PHP_URL_PATH ), '/' ) ?: '/'; // Path needs to be for the whole current subsite
		$cookie_domain = defined( 'COOKIE_DOMAIN' ) ? COOKIE_DOMAIN : '/';

		$cookie_options = array(
			'expires'  => $expires,
			'path'     => $cookiepath,
			'domain'   => $cookie_domain,
			'secure'   => is_ssl(),
			'samesite' => 'None',
			'httponly' => $http_only,
		);

		return setcookie( $cookie_name, $cookie_data, $cookie_options );
	}

	/**
	 * Clears a cookie by setting it to expire in the past.
	 *
	 * @param string $cookie_name The name of the cookie
	 * @param boolean $http_only Whether the cookie should be HTTP only, which means it can't be accessed by JavaScript
	 * @return boolean
	 */
	public function clear_cookie( string $cookie_name, bool $http_only = true ): bool {
		return $this->set_cookie( $cookie_name, '', time() - YEAR_IN_SECONDS, $http_only );
	}
}
