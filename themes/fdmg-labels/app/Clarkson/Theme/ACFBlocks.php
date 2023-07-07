<?php
namespace Clarkson\Theme;

use Clarkson\Theme\ACFBlocks\ArticlesInColumns;
use Clarkson\Theme\ACFBlocks\CTA;
use Clarkson\Theme\ACFBlocks\FeaturedArticle;
use Clarkson\Theme\ACFBlocks\HighlightedContent;
use Clarkson\Theme\ACFBlocks\Partners;
use Clarkson\Theme\ACFBlocks\RelatedArticles;
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
	);

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_acf_block_types' ) );
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
	 * Register ACF GB blocks here
	 * https://www.advancedcustomfields.com/resources/acf_register_block_type/
	 */
	public function register_acf_block_types(): void {
		if ( ! function_exists( 'acf_register_block_type' ) ) {
			return;
		};

		acf_register_block_type(
			array(
				'name'            => 'block-advertisement',
				'title'           => _x( 'Advertisement', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays an advertisement.', 'Block', 'fdmg-labels' ),
				'icon'            => 'schedule',
				'align'           => 'full', // full, wide, left, center, right
				'keywords'        => array( 'advertisement', 'ad' ),
				'supports'        => array(
					'align'    => array( 'full' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'block-social-share',
				'title'           => _x( 'Social share', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays a list of social share buttons.', 'Block', 'fdmg-labels' ),
				'icon'            => 'share',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'social share', 'facebook', 'twitter', 'linkedin', 'email', 'mail' ),
				'post_types'      => array( 'post' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => false,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'widget-vacancies',
				'title'           => _x( 'Vacancies/Jobs', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays a list of vacancies based on multiple options.', 'Block', 'fdmg-labels' ),
				'icon'            => 'money',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'vacancies', 'jobs' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'  => base_block::PREVIEW_MODE_DATA,
							'is_fill_disabled' => false,
							'amount'           => 2,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'widget-latest-and-most-read-posts',
				'title'           => _x( 'Latest and most read posts', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays a list of latest and most read posts in a tabbed component.', 'Block', 'fdmg-labels' ),
				'icon'            => 'star-filled',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'posts', 'latest', 'most read', 'popular' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'widget-newsletter',
				'title'           => _x( 'Newsletter', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays a newsletter block. The content for this block can be edited on the Theme Options page.', 'Block', 'fdmg-labels' ),
				'icon'            => 'email-alt',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'newsletter', 'subscribe', 'signup', 'call to action', 'cta' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);

		acf_register_block_type(
			array(
				'name'            => 'widget-latest-articles',
				'title'           => _x( 'Latest articles', 'Block', 'fdmg-labels' ),
				'description'     => _x( 'Displays a latest articles block. This block will automatically show the latest articles.', 'Block', 'fdmg-labels' ),
				'icon'            => 'list-view',
				'align'           => 'center', // full, wide, left, center, right
				'keywords'        => array( 'latest', 'posts', 'articles' ),
				'supports'        => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'render_callback' => array( $this, 'block_render_callback' ),
				'example'         => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'amount'          => 4,
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
