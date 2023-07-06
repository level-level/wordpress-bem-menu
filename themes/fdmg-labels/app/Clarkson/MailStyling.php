<?php

namespace Clarkson;

use Clarkson\Theme\Assets;
use Clarkson_Core\Templates;

class MailStyling {
	protected const HOOK_PRIORITY = 9999999;

	public function register_hooks(): void {
		add_filter( 'wp_mail', array( $this, 'add_content' ), self::HOOK_PRIORITY );
		add_filter( 'wp_mail_content_type', array( $this, 'add_content_type' ), self::HOOK_PRIORITY );
	}

	public function add_content_type(): string {
		return 'text/html';
	}

	public function add_content( array $mail_data ): array {
		$mail_data['message'] = $this->add_styling( $mail_data );
		return $mail_data;
	}

	/**
	 * Determines if this is a plaintext e-mail or an already styled HTML e-mail
	 * and then passes the content of the message into twig.
	 */
	public function add_styling( array $mail_data ): string {
		// Extract content from complete HTML e-mails
		$content              = html_entity_decode( $mail_data['message'] );
		$content              = preg_split( '/<body(.)*>/', $content );
		$content              = is_array( $content ) ? (string) array_pop( $content ) : '';
		$content              = explode( '</body>', $content );
		$content              = array_shift( $content );
		$mail_data['message'] = $content;
		// Add line breaks to text emails
		if ( $this->is_plaintext( $mail_data['message'] ) ) {
			$mail_data['message'] = nl2br( esc_html( $mail_data['message'] ) );
		}
		$twig = Templates::get_instance();
		return $twig->render_twig(
			'mail/common/styling.twig',
			array(
				'mail_data'      => $mail_data,
				'email_settings' => array(),
				'assets'         => new Assets(),
			),
			true
		);
	}

	public function is_plaintext( string $mailcontent ): bool {
		if ( strip_tags( $mailcontent ) === $mailcontent ) { // phpcs:ignore WordPress.WP.AlternativeFunctions.strip_tags_strip_tags
			return true;
		}
		return false;
	}
}
