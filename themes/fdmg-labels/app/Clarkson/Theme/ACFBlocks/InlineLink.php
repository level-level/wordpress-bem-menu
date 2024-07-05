<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Post;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class InlineLink {
	public const BLOCK_NAME = 'inline-link';

	protected const FIELD_GROUP = 'block_inline_link';

	public const BLOCK_META_TITLE = 'title';
	public const BLOCK_META_LINK  = 'link';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Inline link', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a inline link inside an article.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'admin-links',
				'align'       => 'right', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'link', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'read also', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'inline link', 'Keyword', 'fdmg-labels-admin' ),
				),
				'post_types'  => array( Post::POST_TYPE_NAME ),
				'supports'    => array(
					'align'    => array( 'center', 'left', 'right' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'      => base_block::PREVIEW_MODE_DATA,
							self::BLOCK_META_TITLE => 'Read also',
							self::BLOCK_META_LINK  => array(
								'title' => 'Link 1',
								'url'   => '#',
							),
						),
					),
				),
			),
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Links', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: "Read also"', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
					Link::make( _x( 'Link title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_LINK )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
