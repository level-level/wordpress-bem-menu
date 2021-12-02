<?php

namespace ImpactInvestor\Connectors\Selligent\Admin;

use Clarkson_Core\Templates;
use WordPlate\Acf\Fields\Email;
use WordPlate\Acf\Fields\Message;
use WordPlate\Acf\Location;

class Status {
	public const FIELD_EMAIL   = 'ii_newsletter_status_email';
	public const FIELD_MESSAGE = 'ii_newsletter_status_message';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'create_status_metabox' ) );
	}

	public function create_status_metabox(): void {
		register_extended_field_group(
			array(
				'key'      => 'impact-investor-newsletter-status',
				'title'    => __( 'Newsletter status', 'impact-investor' ),
				'fields'   => array(
					Email::make( __( 'Send errors to the following e-mail address:', 'impact-investor' ), self::FIELD_EMAIL )
						->instructions( __( 'When there is a problem with sending the newsletter, the given e-mail address will receive a copy of the error message.', 'impact-investor' ) ),
					Message::make( __( 'Status', 'impact-investor' ), self::FIELD_MESSAGE )
						->message( $this->get_newsletter_status() ),
				),
				'location' => array(
					Location::if( 'options_page', 'impact-investor-newsletter' ),
				),
				'position' => 'side',
			)
		);
	}

	/**
	 * Shows information about the last newsletter that has been sent.
	 */
	protected function get_newsletter_status(): string {
		$status = get_option( Settings::META_STATUS );

		if ( ! $status ) {
			return 'No newsletter has been sent yet.';
		}

		return Templates::get_instance()->render_twig(
			'partials/admin/selligent/status.twig',
			array(
				'status'  => $status,
				'message' => get_option( Settings::META_MESSAGE ),
			)
		);
	}
}
