<?php
namespace FDMGLabels\StaticPages\Profile;

use FDMGLabels\StaticPages\BaseMainStaticPageController;

class ProfileController extends BaseMainStaticPageController {

	public function get_slug(): string {
		return 'profile';
	}

	public function get_children(): array {
		return array(
			'auth' => new AuthController( $this ),
		);
	}

	/**
	 * Handle all requests of this controller and it's children.
	 */
	public function handle_request( string $query_var ): void {
		foreach ( $this->get_children() as $child ) {
			$child->handle_request( $query_var );
		}
	}
}
