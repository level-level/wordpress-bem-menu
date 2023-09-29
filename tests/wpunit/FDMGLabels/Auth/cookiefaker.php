<?php

namespace FDMGLabels\Auth;

if ( ! function_exists( '\\FDMGLabels\\Auth\\setcookie' ) ) {
	/**
	 * Overwrite php setcookie function in the \FDMGLabels\Auth namespace.
	 * Because of stubs being stupid, the value of $data['expires'] can be empty.
	 * Instead, just clear the cookie if the value is empty.
	 */
	function setcookie( string $name, string $value, array $data ): void {
		if ( empty( $value ) ) {
			unset( $_COOKIE[ $name ] );
		} else {
			$_COOKIE[ $name ] = $value;
		}
	}
}
