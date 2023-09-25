<?php
namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use WP_Term;
use WPSEO_Primary_Term;

class base_object extends Clarkson_Object {

	/**
	 * Get WPSEO (Yoast) primary term or first term from array
	 *
	 * @return mixed
	 */
	public function get_primary_term( string $taxonomy ) {
		if ( class_exists( 'WPSEO_Primary_Term' ) ) {
			$core_objects       = Objects::get_instance();
			$wpseo_primary_term = new WPSEO_Primary_Term( $taxonomy, $this->get_id() );
			$primary_term_id    = $wpseo_primary_term->get_primary_term();

			if ( $primary_term_id ) {
				$term = get_term( (int) $primary_term_id );

				if ( $term instanceof WP_Term ) {
					return $core_objects->get_term( $term );
				}
			}
		}

		$terms = (array) $this->get_terms( $taxonomy ) ?: array();

		if ( count( $terms ) > 0 ) {
			return $terms[0];
		}

		return null;
	}

	/**
	 * @return array<string,string>
	 */
	public function get_publish_date( string $date_format = '' ): array {
		$date_format = $date_format ?: get_option( 'date_format' );

		return array(
			'date'      => $this->get_date_i18n( $date_format ),
			'datetime'  => $this->get_date_i18n( 'Y-m-d' ),
			'date_full' => $this->get_date_i18n( 'F j Y' ),
			'readable'  => $this->get_human_readable_date(),
		);
	}

	/**
	 * Display the publish date of the post in a human readable format if it's been published for less than 24 hours
	 */
	public function get_human_readable_date(): string {
		$date_str     = $this->get_date();
		$date         = (int) $date_str;
		$current_time = time();
		$time_diff    = $current_time - $date;

		if ( $time_diff >= 86400 ) { // 86400 seconds = 24 hours
			return date_i18n( get_option( 'date_format' ), $date );
		} else {
			return sprintf( _x( '%s <span class="sr-only">ago</span>', 'Date', 'fdmg-labels' ), human_time_diff( $date, $current_time ) );
		}
	}
}
