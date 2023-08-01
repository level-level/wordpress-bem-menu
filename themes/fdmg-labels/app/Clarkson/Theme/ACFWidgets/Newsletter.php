<?php
namespace Clarkson\Theme\ACFWidgets;

use Gutenberg\Blocks\base_block;

class Newsletter {
	public const WIDGET_NAME = 'newsletter';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Newsletter', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a newsletter block. The content for this block can be edited on the Theme Options page.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'email-alt',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'newsletter', 'subscribe', 'signup', 'call to action', 'cta' ),
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
