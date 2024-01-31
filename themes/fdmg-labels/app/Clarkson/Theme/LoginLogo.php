<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFPages\SiteSettings;
use Clarkson_Core\Templates;

class LoginLogo {
	public function register_hooks(): void {
		add_action( 'login_enqueue_scripts', array( $this, 'add_custom_logo' ) );
		add_filter( 'login_headerurl', array( $this, 'change_header_url' ) );
	}

	public function add_custom_logo(): void {
		$template = Templates::get_instance();
		$assets   = new Assets();
		$theme    = get_option( sprintf( 'options_%s_%s', SiteSettings::OPTION_GROUP, SiteSettings::OPTION_ACTIVE_THEME ) );

		if ( ! $theme ) {
			return;
		}

		$logo_url = $assets->get_assets_directory_url() . '/images/logo-' . $theme . '.svg';
		$template->echo_twig( 'admin/login-logo.twig', array( 'logo_url' => $logo_url ) );
	}

	/**
	 * Change the login logo url to the home page
	 *
	 * @return string
	 */
	public function change_header_url(): string {
		return esc_url( home_url( '/' ) );
	}
}
