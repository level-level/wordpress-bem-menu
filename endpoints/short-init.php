<?php

// Load WordPress using shortinit
define( 'SHORTINIT', true );
$wp_path = realpath( __DIR__ . '/../../wp-load.php' );
if ( ! $wp_path ) {
	$wp_path = '/srv/http/shared/www/wp-load.php';
}
require $wp_path;

// Load Composer autoloader
require realpath( __DIR__ . '/../vendor/autoload.php' );

/**
 * Function to validate if the referer is from the same domain as the current site.
 */
function fdmg_labels_validate_referer(): void {
	$referer = wp_get_referer();
	$debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
	if ( ! $debugging && ( ! $referer || ! str_contains( $referer, home_url() ) ) ) {
		wp_send_json_error( new WP_Error( 'invalid_referer', 'Invalid referer', array(
			'status' => 403,
		) ) );
	}
}
