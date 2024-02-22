<?php

namespace FDMGLabels\Auth;

use FDMGLabels\CookieHelper;
use WP_Session_Tokens;
use WP_User;

class AuthCookieHelper extends CookieHelper {

	/**
	 * Generate data for a cookie that should behave like the
	 * WordPress auth cookie.
	 * Inspired by the wp_generate_auth_cookie() function.
	 * @param int $user_id
	 * @param array<string,mixed> $data
	 * @param int $expiration
	 * @param string $token
	 * @return string
	 */
	public function build_cookie_data( int $user_id, array $data, int $expiration, string $token ): string {
		$data = array(
			'expiration' => $expiration,
			'token'      => $token,
			'hash'       => $this->get_cookie_hash( $user_id, $data, $expiration, $token ),
			'data'       => $data,
		);
		return wp_json_encode( $data ) ?: '';
	}

	/**
	 * Validate the data in a cookie.
	 * Inspired by the wp_validate_auth_cookie() function.
	 */
	public function validate_cookie_data( mixed $cookie_data, int $user_id ): bool {
		$parsed_cookie_data = $this->parse_cookie_data( $cookie_data );
		if ( ! is_array( $parsed_cookie_data ) ) {
			return false;
		}

		if ( $parsed_cookie_data['expiration'] < time() ) {
			return false;
		}

		$hash = $this->get_cookie_hash(
			$user_id,
			$parsed_cookie_data['data'],
			$parsed_cookie_data['expiration'],
			$parsed_cookie_data['token']
		);
		if ( $parsed_cookie_data['hash'] !== $hash ) {
			return false;
		}

		return true;
	}

	/**
	 * Parses the data from the cookie.
	 * Inspired by the wp_parse_auth_cookie() function.
	 *
	 * @param mixed $cookie_data
	 * @return array{
	 *     expiration: int,
	 *     scheme: string,
	 *     token: string,
	 *     hash: string,
	 *     data: array<string,mixed>
	 * }|null
	 */
	protected function parse_cookie_data( mixed $cookie_data ): ?array {
		// If the cookie is not a string, it can't be valid.
		if ( ! is_string( $cookie_data ) ) {
			return null;
		}

		// Check if cookie data is JSON format.
		$cookie_data = json_decode( $cookie_data, true );
		if ( ! is_array( $cookie_data ) ) {
			return null;
		}

		// Check all required JSON properties exist
		$required_keys = array( 'expiration', 'scheme', 'token', 'hash', 'data' );
		foreach ( $required_keys as $key ) {
			if ( ! array_key_exists( $key, $cookie_data ) ) {
				return null;
			}
		}

		// Check if all properties are of the correct type
		if (
			! is_int( $cookie_data['expiration'] )
			|| ! is_string( $cookie_data['scheme'] )
			|| ! is_string( $cookie_data['token'] )
			|| ! is_string( $cookie_data['hash'] )
			|| ! is_array( $cookie_data['data'] )
		) {
			return null;
		}

		return $cookie_data;
	}

	/**
	 * Function to generate a cookie hash from cookie data.
	 *
	 * @param integer $user_id
	 * @param array<string,mixed> $data Content of the cookie in the data propertry.
	 * @param integer $expiration
	 * @return string
	 */
	protected function get_cookie_hash( int $user_id, array $data, int $expiration, string $token ): string {
		$manager       = WP_Session_Tokens::get_instance( $user_id );
		$token         = $manager->create( $expiration );
		$hash_key_data = $expiration . '|' . $token;

		$user = get_userdata( $user_id );
		if ( $user instanceof WP_User ) {
			$pass_frag     = substr( $user->user_pass, 8, 4 );
			$hash_key_data = $user->user_login . '|' . $pass_frag . '|' . $expiration . '|' . $token;
		}
		$hash_key = wp_hash( $hash_key_data, 'auth' );

		$algo = function_exists( 'hash' ) ? 'sha256' : 'sha1';
		return hash_hmac( $algo, ( wp_json_encode( $data ) ?: '' ) . '|' . $expiration . '|' . $token, $hash_key );
	}
}
