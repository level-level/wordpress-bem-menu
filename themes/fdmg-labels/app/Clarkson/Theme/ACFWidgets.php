<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFWidgets\LatestAndMostReadPosts;
use Clarkson\Theme\ACFWidgets\LatestArticles;
use Clarkson\Theme\ACFWidgets\Newsletter;
use Clarkson\Theme\ACFWidgets\Vacancies;
use Clarkson_Core\Gutenberg\Block_Manager;
use WP_Block_Type;
use WP_Block_Type_Registry;

class ACFWidgets {

	protected const ACF_WIDGETS = array(
		Vacancies::class,
		LatestAndMostReadPosts::class,
		Newsletter::class,
		LatestArticles::class,
	);

	public function register_hooks(): void {
		add_filter( 'acf/register_block_type_args', array( $this, 'set_default_widgets_type_args' ) );

		$this->register_acf_widgets();
	}

	public function register_acf_widgets(): void {
		foreach ( self::ACF_WIDGETS as $field ) {
			( new $field() )->register_hooks();
		}
	}

	/**
	 * Set the default block type args
	 * Available supports settings: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
	 * @param array<string,mixed> $block
	 * @return array<string,mixed>
	 */
	public function set_default_widgets_type_args( array $block ): array {
		$block['category']                    = 'fdmg-labels';
		$block['mode']                        = 'preview'; // auto, preview, edit
		$block['render_callback']             = $block['render_callback'] ?: array( $this, 'widget_render_callback' );
		$block['supports']['customClassName'] = false;

		return $block;
	}

	/**
	 * @param array<string,mixed> $block
	 * @param string $content
	 * @param mixed  $is_preview (WP_Block $block or with acf it is a bool $is_preview)
	 * @param mixed  $post_id (Post id or can also be a string in case of a Widget)
	 */
	public function widget_render_callback( $block, $content, $is_preview = false, $post_id = 0 ): void {
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
