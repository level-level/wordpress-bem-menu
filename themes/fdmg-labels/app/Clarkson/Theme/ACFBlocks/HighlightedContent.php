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
				'title'       => _x( 'Highlighted content', 'Block', 'fdmg-labels' ),
				'description' => _x( 'Displays content in a box to make it stand out more.', 'Block', 'fdmg-labels' ),
				'icon'        => 'text-page',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'highlight', 'summary', 'in short', 'content' ),
				'supports'    => array(
					'align'    => array( 'center' ),
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
				'title'    => _x( 'Highlighted content', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: \'In short\'', 'Fields', 'fdmg-labels' ), self::BLOCK_META_TITLE ),
					WysiwygEditor::make( _x( 'Content', 'Fields', 'fdmg-labels' ), self::BLOCK_META_CONTENT )
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
