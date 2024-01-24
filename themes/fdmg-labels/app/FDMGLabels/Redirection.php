<?php
namespace FDMGLabels;

class Redirection {

	public function register_hooks(): void {
		add_filter( 'redirection_role', array( $this, 'allow_redirection_access' ) );
	}

	/**
	 * Allow everyone with edit_posts capability to manage redirects
	 */
	public function allow_redirection_access( string $role ): string {
		return 'edit_posts';
	}
}
