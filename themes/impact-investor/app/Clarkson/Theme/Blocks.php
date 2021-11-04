<?php
namespace Clarkson\Theme;

use WP_Block_Type_Registry;

class Blocks {
	public function register_hooks(): void {
		$this->register_gutenberg_blocks();
		add_filter( 'allowed_block_types_all', array( $this, 'allowed_gutenberg_blocks' ) );
		add_filter( 'block_categories_all', array( $this, 'create_custom_gutenberg_categories' ) );
		add_filter( 'render_block', array( $this, 'block_wrapper' ), 10, 2 );
	}

	/**
	 * Create custom Gutenberg Category
	 */
	public function create_custom_gutenberg_categories( array $categories ): array {
		return array_merge(
			$categories,
			array(
				array(
					'slug'  => 'impact-investor',
					'title' => _x( 'Impact Investor', 'Block', 'impact-investor' ),
				),
			)
		);
	}

	/**
	 * Allow always custom ACF blocks and some default core and plugin blocks
	 *
	 * @param bool|array $allowed_blocks
	 *
	 * @return array
	 */
	public function allowed_gutenberg_blocks( $allowed_blocks ): array {
		$registered_blocks = WP_Block_Type_Registry::get_instance()->get_all_registered();
		$custom_blocks     = array();

		// Default block type for all ACF blocks
		$block_type = 'acf/block';

		// Define allowed core or plugin blocks
		$allowed_blocks = array(
			// Common
			'core/paragraph',
			'core/image',
			'core/heading',
			'core/list',
			'core/quote',
			'core/table',

			// Plugins
			'gravityforms/form',

			// Formatting
			'core/freeform',
			'core/code',

			// Widgets
			'core/shortcode',

			// Embeds
			'core/embed',  // Specific embeds are defined in editor.js

			// Reusable blocks
			'core/block',
			'core/template',
		);

		// Determine the widget and customiser page
		global $pagenow;
		if ( 'widgets.php' === $pagenow || 'customize.php' === $pagenow ) {

			// Define the allowed blocks that are limited to the widget area
			$allowed_blocks = array(
				'acf/block-advertisement',
			);
			$block_type     = 'acf/widget';
		}

		foreach ( $registered_blocks as $block ) {
			// Add all custom ACF blocks
			if ( strpos( $block->name, $block_type ) === 0 || strpos( $block->name, 'll' ) === 0 ) {
				$custom_blocks[] = $block->name;
			}
		}

		return array_merge( $allowed_blocks, $custom_blocks );
	}

	/**
	* Register custom Gutenberg blocks
	*/
	public function register_gutenberg_blocks(): void {
		if ( ! empty( $this->custom_blocks ) ) {
			foreach ( $this->custom_blocks as $block ) {
				register_block_type( $block );
			}
		}
	}

	/**
	 * Wrap specific Gutenberg blocks in their own divs
	 */
	public function block_wrapper( string $block_content, array $block ): string {
		if ( 'core/table' === $block['blockName'] ) {
			return '<div data-module="table">' . $block_content . '</div>';
		}

		return $block_content;
	}
}
