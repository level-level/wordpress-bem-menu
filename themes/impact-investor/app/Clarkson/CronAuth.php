<?php
namespace Clarkson;

class CronAuth {
	public function register_hooks(): void {
		if ( defined( 'WP_CRON_CUSTOM_HTTP_BASIC_USERNAME' ) && defined( 'WP_CRON_CUSTOM_HTTP_BASIC_PASSWORD' ) ) {
			add_filter( 'cron_request', array( $this, 'authenticate_cron_request' ) );
			add_filter( 'searchwp\indexer\alternate', '__return_true' );
		}
	}

	/**
	 * Adds basic authentication to cron requests.
	 * For test and staging environments which might be
	 * password protected.
	 */
	public static function authenticate_cron_request( array $cron_request ): array {
		$headers                         = array( 'Authorization' => sprintf( 'Basic %s', base64_encode( constant( 'WP_CRON_CUSTOM_HTTP_BASIC_USERNAME' ) . ':' . constant( 'WP_CRON_CUSTOM_HTTP_BASIC_PASSWORD' ) ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
		$cron_request['args']['headers'] = isset( $cron_request['args']['headers'] ) ? array_merge( $cron_request['args']['headers'], $headers ) : $headers;
		return $cron_request;
	}
}
