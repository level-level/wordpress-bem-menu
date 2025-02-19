<?php
namespace FDMGLabels\StaticPages;

use Clarkson\Theme\Navigation\Navigation;
use Clarkson_Core\Templates;

class LoginController extends AbstractMainController {

	public const PAGE_LOGIN = '';
	public const PAGE_ERROR = 'login-error';
	public function get_slug(): string {
		return 'login';
	}

	/**
	 * Route to the correct handle function based on the query var.
	 */
	public function handle_request( string $query_var ): void {
		switch ( $query_var ) {
			case self::PAGE_LOGIN:
				$this->set_page_title( __( 'Login', 'fdmg-labels' ) );
				$this->handle_login_link();
				break;
			case self::PAGE_ERROR:
				$this->set_page_title( __( 'Login error', 'fdmg-labels' ) );
				$this->handle_login_error_link();
				break;
		}
	}

	/**
	 * Redirect user to the keyclock login page.
	 */
	protected function handle_login_link(): void {
		// Disable caching
		nocache_headers();

		// Determine redirect url.
		$redirect_to = home_url();
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_REQUEST['redirect_to'] ) ) {
			$redirect_to = $_REQUEST['redirect_to'];
		}
		$add_url_params = array_filter(
			/**
			 * Make sure that the get parameters to include only have values that can be casted to a string
			 * and are not used by the OpenID Connect plugin.
			 * @param mixed|null $param_value
			 * @param string $param_name
			 * @return bool
			 */
			$_GET, function ( $param_value, string $param_name ): bool {
				return ! in_array( $param_name, array( 'response_type', 'scope', 'client_id', 'state', 'redirect_uri' ), true )
				&& is_scalar( $param_value );
			}, ARRAY_FILTER_USE_BOTH
		);
		$add_url_params = array_map( 'strval', $add_url_params );
		$add_url_params = array_map( 'rawurlencode', $add_url_params );
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		// If already logged in, directly go to the redirect url.
		if ( is_user_logged_in() ) {
			$this->redirect_and_exit( $redirect_to, 302, 'FDMG Labels - Profile auth already logged in' );
			return;
		}

		// If authentication is not available, redirect to the home url.
		if ( ! function_exists( 'oidcg_get_authentication_url' ) ) {
			$this->redirect_and_exit( home_url(), 302, 'FDMG Labels - Profile auth OpenID Connect not enabled' );
		}

		// Redirect to the login url.
		$login_url = add_query_arg( $add_url_params, oidcg_get_authentication_url() );
		$this->redirect_and_exit( $login_url, 302, 'FDMG Labels - Profile auth redirect to OpenID Connect', false );
	}

	/**
	 * Get the permalink of the login page.
	 * If chosen, it will redirect to the current url after the user has logged in.
	 *
	 * @param bool|string $redirect_to_current_url False to not redirect. True to redirect to the current url. String to redirect to a specific url.
	 * @return string
	 */
	public function get_login_permalink( $redirect_to_current_url ): string {
		$permalink = $this->get_permalink( self::PAGE_LOGIN );
		if ( $redirect_to_current_url === false ) {
			return $permalink;
		}

		if ( $redirect_to_current_url === true ) {
			$https       = is_ssl() ? 'https' : 'http';
			$host        = $_SERVER['HTTP_HOST'] ?? '';
			$request_uri = $_SERVER['REQUEST_URI'] ?? '';
			$redirect_to = $https . '://' . $host . $request_uri;
		} else {
			$redirect_to = $redirect_to_current_url;
		}

		$permalink = add_query_arg( 'redirect_to', rawurlencode( $redirect_to ), $permalink );
		return $permalink;
	}

	/**
	 * Show the user a login error page containing the provided error details.
	 */
	protected function handle_login_error_link(): void {
		// Disable caching
		nocache_headers();

		$code      = filter_input( INPUT_GET, 'code' ) ?: __( 'Unknown error code', 'fdmg-labels' );
		$message   = filter_input( INPUT_GET, 'message' ) ?: __( 'Unknown error message', 'fdmg-labels' );
		$login_url = $this->get_login_permalink( false );
		$home_url  = home_url();

		Templates::get_instance()->echo_twig(
			'static-pages/login-error.twig', array(
				'navigation'    => new Navigation(),
				'error_code'    => $code,
				'error_message' => $message,
				'login_url'     => $login_url,
				'home_url'      => $home_url,
			)
		);
		exit;
	}

	public function get_login_error_permalink( string $code, string $message ): string {
		$permalink = $this->get_permalink( self::PAGE_ERROR );
		$permalink = add_query_arg(
			array(
				'code'    => rawurlencode( $code ),
				'message' => rawurlencode( $message ),
			), $permalink
		);
		return $permalink;
	}
}
