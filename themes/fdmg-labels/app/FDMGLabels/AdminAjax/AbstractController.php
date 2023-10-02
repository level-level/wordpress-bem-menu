<?php

namespace FDMGLabels\AdminAjax;

use WP_Error;

abstract class AbstractController {
	abstract public function register_hooks(): void;

	/**
	 * Outputs a json error response
	 *
	 * @param array<string,mixed> $data
	 */
	protected function echo_json_error( string $message, int $status_code = 400, array $data = array() ): void {
		$error = new WP_Error(
			$status_code,
			$message,
			wp_parse_args( $data, array( 'status' => $status_code ) )
		);

		wp_send_json_error( $error, $status_code );
	}

	/**
	 * Outputs a json success response
	 *
	 * @param mixed $data
	 */
	protected function echo_json_success( $data, int $status_code = 200 ): void {
		wp_send_json_success( $data, $status_code );
	}

	protected function echo_error_die( string $message, int $status_code = 400 ): void {
		wp_die( esc_html( $message ), '', $status_code ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
