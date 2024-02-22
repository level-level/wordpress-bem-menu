<?php

namespace FDMGLabels\Auth;

class Init {

	public function register_hooks(): void {
		$cookie_helper = new AuthCookieHelper();
		( new EditorCookie( $cookie_helper ) )->register_hooks();
		( new UserCookie( $cookie_helper ) )->register_hooks();
		( new OpenIDConnect() )->register_hooks();

		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
	}

	public function hide_admin_bar( bool $show ): bool {
		if ( ! $show ) {
			return $show;
		}

		if ( current_user_can( 'read' ) ) {
			return $show;
		}

		return false;
	}
}
