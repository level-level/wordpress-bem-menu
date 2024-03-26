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

	/**
	 * Get the value of a cookie.
	 * Please note that a cookie only exists after a page reload,
	 * so if you set a cookie and then immediately try to get it, it won't
	 * be available.
	 *
	 * @param string $cookie_name The name of the cookie
	 * @return null|string|array<mixed> Null if empty, string for one cookie, array for multiple cookies.
	 */
	public function get_cookie( string $cookie_name ) {
		$value = $_COOKIE[ $cookie_name ] ?? null;
		if ( $value === null ) {
			return $value;
		}

		if ( is_array( $value ) ) {
			return array_map( 'stripslashes', $value );
		}
		return stripslashes( $value );
	}

	/**
	 * Check if a cookie exists.
	 * Please note that a cookie only exists after a page reload,
	 * so if you set a cookie and then immediately try to check if it exists,
	 * it won't be available.
	 *
	 * @param string $cookie_name The name of the cookie
	 * @return boolean
	 */
	public function cookie_exists( string $cookie_name ): bool {
		return array_key_exists( $cookie_name, $_COOKIE );
	}
}
