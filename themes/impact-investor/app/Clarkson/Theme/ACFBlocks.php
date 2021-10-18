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
		$block['category']                    = 'level-level';
		$block['mode']                        = 'auto'; // auto, preview, edit
		$block['render_callback']             = array( $this, 'block_render_callback' );
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
				'name'        => 'block-cta',
				'title'       => _x( 'Call to action', 'Block', 'impact-investor' ),
				'description' => _x( 'Displays a call to action. use it to draw the attention of your users.', 'Block', 'impact-investor' ),
				'icon'        => 'megaphone',
				'align'       => 'wide', // full, wide, left, center, right
				'keywords'    => array( 'call to action', 'cta' ),
				'post_types'  => array( 'post', 'page' ),
				'supports'    => array(
					'align'    => array( 'wide', 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'cta'             => array(
								'text'   => _x( 'Call to action text', 'Block', 'impact-investor' ),
								'title'  => _x( 'Call to action title', 'Block', 'impact-investor' ),
								'button' => array(
									'url'   => '#',
									'title' => 'Button',
								),
							),
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'        => 'block-posts',
				'title'       => _x( 'Posts', 'Block', 'impact-investor' ),
				'description' => _x( 'Displays a list of posts based on multiple options.', 'Block', 'impact-investor' ),
				'icon'        => 'admin-post',
				'align'       => 'wide', // full, wide, left, center, right
				'keywords'    => array( 'posts', 'post', 'news' ),
				'supports'    => array(
					'align'    => array( 'wide' ),
					'multiple' => false,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'title'           => _x( 'Blog posts', 'Block', 'impact-investor' ),
							'is_fill_enabled' => true,
							'amount'          => 1,
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
