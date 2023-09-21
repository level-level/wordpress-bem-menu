<?php
namespace Clarkson\Theme;

use Clarkson_Core\Objects;
use WP_Post;
use WP_Term;

class ACF {

	/**
	 * @var Objects $core_objects
	 */
	public $core_objects;

	public function __construct() {
		$this->core_objects = Objects::get_instance();
	}

	public function register_hooks(): void {
		add_filter( 'acf/load_field_group', array( $this, 'prevent_field_group_sync' ) );

		// Priority need to be 11, because acf does something on 10
		add_filter( 'acf/format_value/type=post_object', array( $this, 'convert_post_object' ), 11, 1 );
		add_filter( 'acf/format_value/type=relationship', array( $this, 'convert_relationship_objects' ), 11, 1 );
		add_filter( 'acf/format_value/type=taxonomy', array( $this, 'convert_taxonomy_objects' ), 11, 1 );
		add_filter( 'acf/fields/relationship/query', array( $this, 'order_items_by_date' ), 10, 1 );
	}

	/**
	 * Prevent field groups from syncing when you are not on an development environment (like production).
	 *
	 * @param array<string,mixed> $field_group
	 * @return array<string,mixed>
	 */
	public function prevent_field_group_sync( array $field_group ): array {
		$is_debug   = ( defined( 'WP_DEBUG' ) && (bool) constant( 'WP_DEBUG' ) );
		$force_show = ( true === filter_input( INPUT_GET, 'show_field_group_sync', FILTER_VALIDATE_BOOLEAN ) );
		if ( ! $is_debug && ! $force_show ) {
			$field_group['private'] = true;
		}
		return $field_group;
	}

	/**
	 * Return a Clarkson Object if output value is 'object'
	 * @param mixed $value
	 * @return mixed
	 */
	public function convert_post_object( $value ) {
		// single object
		if ( ! empty( $value ) && $value instanceof WP_Post ) {
			return $this->core_objects->get_object( $value );
		}

		// array
		if ( is_array( $value ) ) {
			if ( ! empty( $value[0] ) && $value[0] instanceof WP_Post ) {
				return $this->core_objects->get_objects( $value );
			}
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function convert_relationship_objects( $value ) {
		if ( is_array( $value ) ) {
			if ( ! empty( $value[0] ) && $value[0] instanceof WP_Post ) {
				return $this->core_objects->get_objects( $value );
			}
		}

		return $value;
	}

	/**
	 * Covert default WP_Term to Clarkson_Term
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	public function convert_taxonomy_objects( $value ) {

		if ( ! empty( $value ) ) {
			if ( is_array( $value ) ) {
				$terms = array();
				foreach ( $value as $term ) {
					$term = get_term( $term );

					if ( $term instanceof WP_Term ) {
						$terms[] = $this->core_objects->get_term( $term );
					}
				}

				return $terms;
			} else {
				$term = get_term( $value );

				if ( $term instanceof WP_Term ) {
					return $this->core_objects->get_term( $term );
				}
			}
		}

		return $value;
	}

	/**
	 * Order relationship items by date
	 * @param array<string,mixed> $args
	 * @return array<string,mixed>
	 */
	public function order_items_by_date( $args ) {
		$args['orderby'] = 'date';
		$args['order']   = 'DESC';
		return $args;
	}
}
