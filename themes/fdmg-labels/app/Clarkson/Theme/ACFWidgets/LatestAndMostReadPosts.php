<?php
namespace Clarkson\Theme\ACFWidgets;

use Gutenberg\Blocks\base_block;

class LatestAndMostReadPosts {
	public const WIDGET_NAME = 'latest-and-most-read-posts';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Latest and most read posts', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of latest and most read posts in a tabbed component.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'star-filled',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'posts', 'latest', 'most read', 'popular' ),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
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
