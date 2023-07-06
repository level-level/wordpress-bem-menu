<?php

namespace Gutenberg\Blocks;

use Clarkson\Theme\SVGIcons;
use Clarkson_Core\Gutenberg\Block_Type;
use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use Exception;
use WP_Error;

class base_block extends Block_Type {
	public const PREVIEW_MODE_IMAGE = 'image';
	public const PREVIEW_MODE_DATA  = 'data';
	public const PREVIEW_MODE_NONE  = 'none';

	/**
	 * @var array|bool $content_attributes
	 */
	public $content_attributes = false;

	/**
	 * @var int $post_id
	 */
	public $post_id = 0;

	/**
	 * @var string $title
	 */
	public $title = '';

	/**
	 * @var bool $is_acf
	 */
	public $is_acf = false;

	/**
	 * @var bool $is_preview
	 */
	public $is_preview = false;

	/**
	 * @var callable|null
	 */
	public $original_render_callback;

	/**
	 * Constructor.
	 *
	 * Will populate object properties from the provided arguments.
	 * @see \WP_Block_Type::__construct()
	 *
	 * @param string       $block_type Block type name including namespace.
	 * @param array|string $args       Optional. Array or string of arguments for registering a block type.
	 *                                 Default empty array.
	 */
	public function __construct( string $block_type, $args = array() ) {
		// Set props in constructor
		$this->attributes = array();

		// Let \WP_Block_Type overwrite variables
		parent::__construct( $block_type, $args );
	}

	/**
	 * Allows filtering of the path where this blocks twig file is found.
	 *
	 * @return string
	 */
	public function get_twig_template_path() {
		$block_directory = apply_filters( 'clarkson_core_gutenberg_block_template_directory', '/templates/partials/blocks/gb/' );
		return apply_filters( 'clarkson_core_gutenberg_block_template_' . $this->name, get_stylesheet_directory() . $block_directory . $this->name . '.twig', $this );
	}

	/**
	 * Tries to find a twig file to use for rendering. If the twig file doesn't
	 * exists it falls back to the original render callback.
	 *
	 * @param array  $attributes
	 * @param string $content
	 * @param mixed  $block (WP_Block $block or with acf it is a bool $is_preview)
	 * @param mixed  $post_id
	 * @return string
	 */
	public function clarkson_render_callback( $attributes, $content, $block = null, $post_id = 0 ) {
		if ( file_exists( $this->get_twig_template_path() ) ) {
			$cc_template = Templates::get_instance();

			$data = null;

			if ( $this->is_preview( $block, $attributes ) && $this->has_example( $attributes ) ) {
				$this->is_preview = true;

				if ( $this->get_preview_mode( $attributes ) === self::PREVIEW_MODE_IMAGE ) {
					$preview_image = $this->get_preview_image();
					if ( empty( $preview_image ) ) {
						return '';
					}
					return sprintf( '<img src="%s" />', $preview_image );
				} elseif ( $this->get_preview_mode( $attributes ) === self::PREVIEW_MODE_DATA ) {
					if ( ! empty( $attributes['data'] ) && ! empty( $attributes['example'] ) ) {
						$data = $attributes['data'];
					}
				}
			}

			// Only in the acf render callback we get a post_id
			if ( empty( $post_id ) ) {
				$post_id = get_the_ID();
			}
			$this->post_id = $post_id;

			if ( ! isset( $attributes['data'] ) ) {
				$attributes['data'] = array();
			}

			if ( is_null( $data ) ) {
				try {
					if ( isset( $attributes['id'] ) ) {
						acf_setup_meta( $attributes['data'], $attributes['id'], true );
						$data = get_fields( $attributes['id'] );
						acf_reset_meta( $attributes['id'] );
						$this->is_acf = true;
					}
				} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
					// This is not an ACF block.
				}
			}

			$this->content_attributes = $data;

			$this->title = $attributes['title'] ?? '';

			$block_args = array(
				'data'       => $this->content_attributes,
				'content'    => $content,
				'block'      => $this,
				'align'      => $attributes['align'] ?? '',
				'post_id'    => $this->post_id,
				'is_acf'     => $this->is_acf,
				'is_preview' => $this->is_preview,
				'svg_icons'  => SVGIcons::get_instance(),
			);

			$block_args = apply_filters( 'clarkson_core_block_context_' . $this->name, $block_args, $this );

			$errors = $this->validate( $attributes );

			if ( $errors->has_errors() ) {
				$block_args['errors'] = $errors;
				return $cc_template->render_twig( 'partials/components/gb-invalid.twig', $block_args, true );
			}

			$block_content = $cc_template->render_twig( $this->get_twig_template_path(), $block_args, true );

			if ( is_admin() && $block === true ) {
				return '<div class="clarkson-acf-preview-block">' . $block_content . '</div>';
			} else {
				return $block_content;
			}
		}
		if ( is_callable( $this->original_render_callback ) ) {
			return (string) call_user_func( $this->original_render_callback, $attributes, $content );
		}

		return $content;
	}

	protected function get_preview_image(): ?string {
		$filename = realpath( $this->get_twig_template_path() . '-preview.png' );
		if ( file_exists( $filename ) ) {
			$filename = str_replace( array( realpath( get_stylesheet_directory() ), realpath( get_template_directory() ) ), '', $filename );
			$filename = get_stylesheet_directory_uri() . $filename;
			return $filename;
		}
		return null;
	}

	/**
	 * @param array $attributes
	 */
	protected function get_preview_mode( $attributes ): string {
		if ( isset( $attributes['data']['ll-preview-mode'] ) && $attributes['data']['ll-preview-mode'] ) {
			return $attributes['data']['ll-preview-mode'];
		}
		return self::PREVIEW_MODE_NONE;
	}

	/**
	 * @param array $attributes
	 */
	protected function has_example( $attributes ): bool {
		return ! empty( $attributes['example'] );
	}

	/**
	 * @param mixed  $block (WP_Block $block or with acf it is a bool $is_preview)
	 * @param array $attributes
	 */
	protected function is_preview( $block, $attributes ): bool {
		return is_bool( $block ) && $block && $this->get_preview_mode( $attributes ) !== self::PREVIEW_MODE_NONE;
	}

	/**
	 * @param array $attributes
	 */
	public function validate( $attributes ): WP_Error {
		$errors = new WP_Error();
		foreach ( $attributes['data'] as  $key => $attribute ) {
			$search = 'field_';
			if ( substr( $key, 0, strlen( $search ) ) === $search ) {
				$field_object = get_field_object( $key, false, false, false );
				if ( $field_object ) {
					acf_validate_value( $attribute, $field_object, $key );
					$acf_errors = acf_get_validation_errors() ?: array();
					foreach ( $acf_errors as $acf_error ) {
						$matches = array();
						preg_match_all( '/field_[a-z0-9]+/', $acf_error['input'], $matches );
						$match_fields = array();
						foreach ( $matches[0] as $match ) {
							$match_field = get_field_object( $match, false, false, false );
							if ( $match_field ) {
								$match_fields[] = $match_field['label'];
							}
						}
						$errors->add( $acf_error['input'] . uniqid(), sprintf( '%s: %s', implode( ' / ', $match_fields ), $acf_error['message'] ) );
					}
					acf_reset_validation_errors();
				}
			}
		}
		return $errors;
	}

	/**
	 * Create query args based on given terms, post types and excluded objects
	 */
	public function get_query_args( array $terms, array $post_types, array $exluded_objects = array(), int $count = 2, string $tax_relation = 'OR' ): array {
		$exclude_ids = array_map(
			function( Clarkson_Object $object ) {
				return $object->get_id();
			},
			$exluded_objects
		);

		array_push( $exclude_ids, $this->post_id );
		$posts_per_page = max( 0, $count - count( $exluded_objects ) );

		if ( $count === -1 ) {
			$posts_per_page = -1;
		}

		$args = array(
			'post_status'    => 'publish',
			'post__not_in'   => $exclude_ids,
			'posts_per_page' => $posts_per_page,
			'post_type'      => $post_types,
			'tax_query'      => array(
				'relation' => $tax_relation,
			),
		);

		if ( $posts_per_page === 0 ) {
			return array();
		}

		foreach ( $terms as $term ) {
			if ( $term instanceof Clarkson_Term ) {
				$args['tax_query'][] = array(
					'taxonomy' => $term->get_taxonomy(),
					'field'    => 'term_taxonomy_id',
					'terms'    => $term->get_term_taxonomy_id(),
				);
			}
		}

		return $args;
	}

	public function get_object(): ?Clarkson_Object {
		return Clarkson_Object::get( $this->post_id );
	}
}
