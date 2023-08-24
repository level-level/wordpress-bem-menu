<?php
namespace Clarkson\WPadmin;

use WP_Role;

class Capabilities {

	// WordPress default roles
	public const ROLE_ADMINISTRATOR = 'administrator';
	public const ROLE_EDITOR        = 'editor';
	public const ROLE_AUTHOR        = 'author';
	public const ROLE_CONTRIBUTOR   = 'contributor';
	public const ROLE_SUBSCRIBER    = 'subscriber';

	// Custom roles
	public const ROLE_CLIENT_ADMINISTRATOR = 'client_administrator';

	public function register_hooks(): void {
		add_action( 'clarkson_update_capabilities', array( $this, 'update_client_admin_role' ) );
	}

	/**
	 * Update client admin role capabilities
	 */
	public function update_client_admin_role(): void {
		remove_role( self::ROLE_CLIENT_ADMINISTRATOR );

		$admin      = get_role( 'administrator' );
		$admin_caps = $admin->capabilities ?? array();
		$role       = add_role( self::ROLE_CLIENT_ADMINISTRATOR, __( 'Client administrator', 'fdmg-labels-admin' ), $admin_caps );
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
