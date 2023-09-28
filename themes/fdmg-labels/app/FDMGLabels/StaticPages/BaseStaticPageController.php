<?php
namespace FDMGLabels\StaticPages;

use Clarkson_Core\Templates;

abstract class BaseStaticPageController {

	abstract public function get_permalink( string $sub_page = '' ): string;

	abstract public function handle_request( string $query_var ): void;

	abstract public function is_current_page( string $slug ): bool;

	/**
	 * Exits running code, except for when we are in a testing environment.
	 */
	protected function maybe_exit(): void {
		if ( defined( 'WP_UNIT_TESTING' ) && constant( 'WP_UNIT_TESTING' ) ) {
			return;
		}
		exit;
	}

	/**
	 * Echo a twig template.
	 * Then exits running code, except for when we are in a testing environment.
	 */
	protected function echo_twig_and_exit( string $template_file, array $objects = array() ): void {
		Templates::get_instance()->echo_twig( $template_file, $objects, true );
		$this->maybe_exit();
	}

	/**
	 * Perform a redirect.
	 * Then exits running code, except for when we are in a testing environment.
	 */
	public function redirect_and_exit( string $location, int $status, string $x_redirect_by ): void {
		wp_safe_redirect( $location, $status, $x_redirect_by ); // phpcs:ignore WordPressVIPMinimum.Security.ExitAfterRedirect.NoExit
		$this->maybe_exit();
	}

	/**
	 * Get the ID from the query var, or 0 if not exists.
	 */
	public function extract_id_from_query_var( string $query_var ): int {
		$matches = array();
		preg_match( '/(\/\d+)/', $query_var, $matches );
		if ( ! empty( $matches[0] ) ) {
			return (int) str_replace( '/', '', $matches[0] );
		}
		return 0;
	}

	/**
	 * Remove the ID from the query var. If not exists, nothing is removed.
	 */
	public function strip_id_from_query_var( string $query_var, int $object_id ): string {
		if ( empty( $object_id ) ) {
			return $query_var;
		}
		$search = sprintf( '/%s', $object_id );
		return str_replace( $search, '', $query_var );
	}

	/**
	 * Get a permalink ending with an ID.
	 */
	public function get_id_permalink( int $object_id, string $sub_page = '' ): string {
		$object_sub_page = sprintf( '%s/%s', $sub_page, $object_id );
		return $this->get_permalink( $object_sub_page );
	}

	public function set_page_title( string $title ): void {
		add_filter(
			'wpseo_title', function( $original_title ) use ( $title ) {
				$separator = function_exists( 'YoastSEO' ) ? YoastSEO()->helpers->options->get_title_separator() : '-';
				return sprintf( '%s %s %s', $title, $separator, get_bloginfo( 'name' ) );
			}
		);
	}
}
