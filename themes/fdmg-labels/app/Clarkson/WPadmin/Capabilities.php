<?php
namespace Clarkson\WPadmin;

use WP_Role;

class Capabilities {
	public function register_hooks(): void {
		add_action( 'clarkson_update_capabilities', array( $this, 'update_client_admin_role' ) );
	}

	/**
	 * Update client admin role capabilities
	 */
	public function update_client_admin_role(): void {
		remove_role( 'client_administrator' );

		$admin      = get_role( 'administrator' );
		$admin_caps = $admin->capabilities ?? array();
		$role       = add_role( 'client_administrator', __( 'Client administrator', 'fdmg-labels' ), $admin_caps );
		if ( ! $role instanceof WP_Role ) {
			return;
		}
		$role->remove_cap( 'activate_plugins' );
		$role->remove_cap( 'delete_plugins' );
		$role->remove_cap( 'delete_themes' );
		$role->remove_cap( 'edit_files' );
		$role->remove_cap( 'edit_plugins' );
		$role->remove_cap( 'edit_themes' );
		$role->remove_cap( 'import' );
		$role->remove_cap( 'install_plugins' );
		$role->remove_cap( 'install_themes' );
		$role->remove_cap( 'switch_themes' );
		$role->remove_cap( 'update_core' );
		$role->remove_cap( 'update_plugins' );
		$role->remove_cap( 'update_themes' );
		$role->remove_cap( 'delete_site' );

		$role->add_cap( 'gform_full_access' );
		$role->add_cap( 'gravityforms_selligent' );
	}
}
