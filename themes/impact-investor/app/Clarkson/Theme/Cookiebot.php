<?php
namespace Clarkson\Theme;

use Clarkson_Core\Templates;
use Cookiebot_WP;

class Cookiebot {
	public function register_hooks(): void {
		$this->register_shortcodes();
	}

	protected function should_load_shortcodes(): bool {
		$load_shortcodes = false;
		if ( class_exists( 'Cookiebot_WP' ) ) {
			$cookiebot = Cookiebot_WP::instance();
			$cbid      = $cookiebot->get_cbid();
			if ( ! empty( $cbid ) ) {
				$load_shortcodes = true;
			}
		}

		return apply_filters( 'clarkson_cookiebot_load_shortcodes', $load_shortcodes );
	}

	protected function register_shortcodes(): void {
		add_shortcode( 'cookiebot_renew', array( $this, 'shortcode_renew' ) );
		add_shortcode( 'cookiebot_withdraw', array( $this, 'shortcode_withdraw' ) );
	}

	/**
	 * @param array|string $atts
	 * @param string|null $content
	 */
	public function shortcode_renew( $atts = array(), $content = null ): string {
		if ( ! $this->should_load_shortcodes() ) {
			return esc_html__( 'Please add your Cookiebot ID to allow changing your cookie consent.', 'impact-investor' );
		}

		if ( empty( $content ) ) {
			$content = __( 'Change your consent', 'impact-investor' );
		}

		$templates = Templates::get_instance();
		return $templates->render_twig(
			'partials/components/cookiebot.twig', array(
				'button_text' => $content,
				'action'      => 'renew',
			), true
		);
	}

	/**
	 * @param array|string $atts
	 * @param string|null $content
	 */
	public function shortcode_withdraw( $atts = array(), $content = null ): string {
		if ( ! $this->should_load_shortcodes() ) {
			return esc_html__( 'Please add your Cookiebot ID to allow revoking your cookie consent.', 'impact-investor' );
		}

		if ( empty( $content ) ) {
			$content = __( 'Revoke your consent', 'impact-investor' );
		}

		$templates = Templates::get_instance();
		return $templates->render_twig(
			'partials/components/cookiebot.twig', array(
				'button_text' => $content,
				'action'      => 'revoke',
			), true
		);
	}
}
