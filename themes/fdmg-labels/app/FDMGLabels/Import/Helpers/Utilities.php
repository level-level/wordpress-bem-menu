<?php
namespace FDMGLabels\Import\Helpers;

class Utilities {

	/**
	 * On development environments we don't want to import real email addresses.
	 * This will prevent us from accidentely sending emails that shouldn't be sent.
	 */
	public function get_development_safe_email( string $email ): string {
		if ( empty( $email ) ) {
			return $email;
		}

		$is_debug = defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' );
		if ( ! $is_debug && wp_get_environment_type() === 'production' ) {
			return $email;
		}

		return sprintf( '%s@fdmg-labels-import.test', sanitize_title_with_dashes( $email ) );
	}

	/**
	 * Transform the FDMG state to a WordPress post status.
	 */
	public function get_post_status( string $state ): string {
		$status = '';
		switch ( $state ) {
			case 'DRAFT':
				$status = 'draft';
				break;
			case 'PUBLISHED':
			case 'EMBARGOED':
				$status = 'publish';
				break;
			case 'DELETED':
				$status = 'thrash';
				break;
		}

		return $status;
	}

}
