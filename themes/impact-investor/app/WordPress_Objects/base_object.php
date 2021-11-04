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
}
