<?php
namespace FDMGLabels\StaticPages;

use Clarkson_Core\Templates;

trait ResponseTrait {

	protected function exit( int $status ): void {
		wp_die( '', $status ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Echo a twig template.
	 * Then exits running code, except for when we are in a testing environment.
	 *
	 * @param string $template_file
	 * @param array<string, mixed> $objects
	 * @return void
	 */
	protected function echo_twig_and_exit( string $template_file, array $objects = array(), int $status = 200 ): void {
		Templates::get_instance()->echo_twig( $template_file, $objects, true );
		$this->exit( $status );
	}

	/**
	 * Perform a redirect.
	 * Then exits running code, except for when we are in a testing environment.
	 */
	protected function redirect_and_exit( string $location, int $status, string $x_redirect_by, bool $safe = true ): void {
		if ( $safe ) {
			wp_safe_redirect( $location, $status, $x_redirect_by ); // phpcs:ignore
		} else {
			wp_redirect( $location, $status, $x_redirect_by ); // phpcs:ignore
		}
		$this->exit( $status );
	}
}
