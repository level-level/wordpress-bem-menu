<?php

namespace FDMGLabels\Auth;

use FDMGLabels\StaticPages\LoginController;

class Init {

	public function __construct(
		private AuthCookieHelper $auth_cookie_helper,
	) {}

	public function register_hooks(): void {
		( new EditorCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new UserCookie( $this->auth_cookie_helper ) )->register_hooks();
		( new OpenIDConnect( $this->auth_cookie_helper ) )->register_hooks();
		( new SessionExpiration() )->register_hooks();

		add_filter( 'show_admin_bar', array( $this, 'hide_admin_bar' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_scripts' ) );
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

	public function localize_scripts(): void {
		wp_localize_script(
			'clarkson_head',
			'auth',
			array(
				'loginUrl' => ( new LoginController() )->get_login_permalink( true ),
			)
		);
	}
}
