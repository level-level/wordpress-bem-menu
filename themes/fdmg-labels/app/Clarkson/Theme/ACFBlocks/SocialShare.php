<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Post;
use Gutenberg\Blocks\base_block;

class SocialShare {
	public const BLOCK_NAME = 'social-share';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Social share', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of social share buttons.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'share',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'social share', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'facebook', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'twitter', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'linkedin', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'email', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'x', 'Keyword', 'fdmg-labels-admin' ),
				),
				'post_types'  => array( Post::POST_TYPE_NAME ),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => false,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);
	}
}
