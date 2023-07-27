<?php
namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use WP_Term;
use WPSEO_Primary_Term;

class base_object extends Clarkson_Object {

	/**
	 * Get WPSEO (Yoast) primary term or first term from array
	 *
	 * @return null|Clarkson_Term
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

		$terms = $this->get_terms( $taxonomy );
		$terms = is_array( $terms ) ? $terms : array();
		return array_shift( $terms );
	}

	/**
	 * Get estimate of reading time in minutes.
	 * The average reading speed of humans is 238 words per minute.
	 * @see https://www.sciencedirect.com/science/article/abs/pii/S0749596X19300786#:~:text=Based%20on%20the%20analysis%20of,and%20260%20wpm%20for%20fiction.
	 *
	 * It uses the raw content instead of the parsed content to determine the amount of words.
	 * This is needed to prevent an endless loop.
	 *
	 * @return int
	 */
	public function get_reading_time_in_minutes(): int {
		$content = wp_strip_all_tags( $this->get_raw_content() );
		$words   = (int) str_word_count( $content );
		$minutes = (int) ceil( $words / 238 );
		return $minutes < 1 ? 1 : $minutes;
	}
}
