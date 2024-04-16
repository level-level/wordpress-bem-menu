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
	$debugging = defined( 'WP_DEBUG' ) && WP_DEBUG;
	if ( $debugging ) {
		return;
	}

	$referer_host = parse_url( wp_get_referer(), PHP_URL_HOST ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
	$current_url  = sprintf(
		'%s://%s%s',
		is_ssl() ? 'https' : 'http',
		$_SERVER['HTTP_HOST'] ?? '',
		$_SERVER['REQUEST_URI'] ?? ''
	);
	$current_host = parse_url( $current_url, PHP_URL_HOST ); // phpcs:ignore WordPress.WP.AlternativeFunctions.parse_url_parse_url
	if ( $referer_host !== $current_host ) {
		wp_send_json_error(
			new WP_Error(
				'invalid_referer', 'Invalid referer', array(
					'status' => 403,
				)
			), 403
		);
	}
}
