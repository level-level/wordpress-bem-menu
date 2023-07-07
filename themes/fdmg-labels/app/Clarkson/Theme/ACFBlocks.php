<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFBlocks\Advertisement;
use Clarkson\Theme\ACFBlocks\ArticlesInColumns;
use Clarkson\Theme\ACFBlocks\CTA;
use Clarkson\Theme\ACFBlocks\FeaturedArticle;
use Clarkson\Theme\ACFBlocks\HighlightedContent;
use Clarkson\Theme\ACFBlocks\Partners;
use Clarkson\Theme\ACFBlocks\RelatedArticles;
use Clarkson\Theme\ACFBlocks\SocialShare;
use Clarkson\Theme\ACFBlocks\Team;
use Clarkson_Core\Gutenberg\Block_Manager;
use Gutenberg\Blocks\base_block;
use WP_Block_Type;
use WP_Block_Type_Registry;

class ACFBlocks {

	protected const ACF_BLOCKS = array(
		CTA::class,
		ArticlesInColumns::class,
		FeaturedArticle::class,
		RelatedArticles::class,
		Team::class,
		Partners::class,
		HighlightedContent::class,
		Advertisement::class,
		SocialShare::class,
	);

	public function register_hooks(): void {
		add_filter( 'acf/register_block_type_args', array( $this, 'set_default_block_type_args' ) );

		$this->register_acf_blocks();
	}

	public function register_acf_blocks(): void {
		foreach ( self::ACF_BLOCKS as $field ) {
			( new $field() )->register_hooks();
		}
	}

	/**
	 * Set the default block type args
	 * Available supports settings: https://developer.wordpress.org/block-editor/reference-guides/block-api/block-supports/
	 */
	public function set_default_block_type_args( array $block ): array {
		$block['category']                    = 'fdmg-labels';
		$block['mode']                        = 'preview'; // auto, preview, edit
		$block['supports']['customClassName'] = false;
		$block['render_callback']             = $block['render_callback'] ?: array( $this, 'block_render_callback' );
		return $block;
	}

	/**
	 * @psalm-suppress InvalidStringClass
	 * @param array  $block
	 * @param string $content
	 * @param mixed  $is_preview (WP_Block $block or with acf it is a bool $is_preview)
	 * @param mixed  $post_id (Post id or can also be a string in case of a Widget)
	 */
	public function block_render_callback( $block, $content, $is_preview = false, $post_id = 0 ): void {
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
