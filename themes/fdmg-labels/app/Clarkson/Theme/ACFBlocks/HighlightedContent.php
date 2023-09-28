<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WysiwygEditor;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class HighlightedContent {
	public const BLOCK_NAME = 'highlighted-content';

	protected const FIELD_GROUP = 'block_highlighted_content';

	public const BLOCK_META_TITLE   = 'title';
	public const BLOCK_META_CONTENT = 'content';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Highlighted content', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays content in a box to make it stand out more.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'text-page',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'highlight', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'summary', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'in short', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center', 'left', 'right' ),
					'multiple' => true,
				),
				'example'     => array(
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

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Highlighted content', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: \'In short\'', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
					WysiwygEditor::make( _x( 'Content', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_CONTENT )
						->required()
						->tabs( 'visual' )
						->toolbar( 'basic' )
						->mediaUpload( false ),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
