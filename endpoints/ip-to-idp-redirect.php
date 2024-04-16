<?php

use Clarkson\Utilities\IPAddress;
use FDMGLabels\Auth\IPAddressIDPRedirect;
use FDMGLabels\StaticPages\LoginController;

require __DIR__ . '/short-init.php';
nocache_headers();
fdmg_labels_validate_referer();

if ( $_SERVER['REQUEST_METHOD'] === 'GET' ) {
	$ip_util            = new IPAddress();
	$visitor_ip_address = $ip_util->get_visitor_ip_address();
	if ( ! $visitor_ip_address || ! $ip_util->validate_ip_address( $visitor_ip_address ) ) {
		wp_send_json_success( array(
			'redirect_url' => null,
		) );
	}

	// If no IDP alias was found, no need to redirect
	$idp_alias = ( new IPAddressIDPRedirect() )->ip_address_to_idp_alias( $visitor_ip_address );
	if ( $idp_alias === null ) {
		wp_send_json_success( array(
			'redirect_url' => null,
		) );
	}

	// Build the redirect URL
	$redirect_to_after_login = $request->get_param( 'redirect_to' );
	$redirect_url            = ( new LoginController() )->get_login_permalink( $redirect_to_after_login );
	$redirect_url            = add_query_arg( 'kc_idp_hint', rawurlencode( $idp_alias ), $redirect_url );

	// Return the response
	wp_send_json_success( array(
		'redirect_url' => $redirect_url,
	) );
}
