<?php
namespace Clarkson\Theme;

use Clarkson_Core\Gutenberg\Block_Manager;
use Gutenberg\Blocks\base_block;
use WP_Block_Type;
use WP_Block_Type_Registry;

class ACFBlocks {

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_acf_block_types' ) );
		add_filter( 'acf/register_block_type_args', array( $this, 'set_default_block_type_args' ) );
	}

	/**
	 * Set the default block type args
	 * Available supports settings: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
	 */
	public function set_default_block_type_args( array $block ): array {
		$block['category']                    = 'impact-investor';
		$block['mode']                        = 'preview'; // auto, preview, edit
		$block['supports']['customClassName'] = false;

		return $block;
	}

	/**
	 * Register ACF GB blocks here
	 * https://www.advancedcustomfields.com/resources/acf_register_block_type/
	 */
	public function register_acf_block_types(): void {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		};

		acf_register_block_type(
			array(
				'name'            => 'block-featured-article',
				'title'           => _x( 'Featured article', 'Block', 'impact-investor' ),
				'description'     => _x( 'Displays a single featured article.', 'Block', 'impact-investor' ),
				'icon'            => 'welcome-view-site',
				'align'           => 'wide', // full, wide, left, center, right
				'keywords'        => array( 'featured article', 'post' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'is_fill_enabled' => true,
							'amount'          => 1,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'block-two-column-articles',
				'title'           => _x( 'Two (2) column articles', 'Block', 'impact-investor' ),
				'description'     => _x( 'Displays a list of articles in a two (2) column layout based on multiple options.', 'Block', 'impact-investor' ),
				'icon'            => 'columns',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'posts', 'articles', 'column', 'two', '2', 'post' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'is_fill_enabled' => true,
							'amount'          => 2,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'block-three-column-articles',
				'title'           => _x( 'Three (3) column articles', 'Block', 'impact-investor' ),
				'description'     => _x( 'Displays a list of articles in a three (3) column layout based on multiple options.', 'Block', 'impact-investor' ),
				'icon'            => 'columns',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'posts', 'articles', 'column', 'three', '3', 'post' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'is_fill_enabled' => true,
							'amount'          => 3,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'block-highlighted-content',
				'title'           => _x( 'Highlighted content', 'Block', 'impact-investor' ),
				'description'     => _x( 'Displays content in a box to make it stand out more.', 'Block', 'impact-investor' ),
				'icon'            => 'text-page',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'highlight', 'summary', 'in short', 'content' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'content'         => '<p>Example feature content</p>',
						),
					),
				),
			)
		);
	}

	/**
	 * Register GB blocks here
	 *
	 * To use custom GB blocks, add the following line to the register_hooks function:
	 * add_action( 'init', array( $this, 'register_block_types' ) );
	 *
	 * https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
	 */
	public function register_block_types(): void {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type(
			'll/block-example', array(
				'name'            => 'block-example',
				'render_callback' => array( $this, 'block_render_callback' ),
			)
		);
	}

	/**
	 * @psalm-suppress InvalidStringClass
	 */
	public function block_render_callback( array $block, string $content, bool $is_preview = false, int $post_id = 0 ): void {
		$block_object = WP_Block_Type_Registry::get_instance()->get_registered( $block['name'] );
		if ( ! $block_object instanceof WP_Block_Type ) {
			return;
		}
		$block_manager  = new Block_Manager();
		$block_class    = $block_manager->determine_block_type_class( $block_object );
		$clarkson_block = new $block_class( $block_object->name );
		echo $clarkson_block->clarkson_render_callback( $block, $content, $is_preview, $post_id ); //phpcs:ignore
	}
}
