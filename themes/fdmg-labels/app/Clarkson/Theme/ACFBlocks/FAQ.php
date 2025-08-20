<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class FAQ {
	public const BLOCK_NAME = 'faq';

	protected const FIELD_GROUP = 'block_faq';

	public const BLOCK_META_TITLE             = 'title';
	public const BLOCK_META_ITEMS             = 'items';
	public const BLOCK_META_CONTENT           = 'content';
	public const BLOCK_META_EXPAND_FIRST_ITEM = 'expand_first_item';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'FAQ', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a frequently asked question.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'excerpt-view',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'faq', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'accordion', 'Keyword', 'fdmg-labels-admin' ),
				),
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

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'FAQ', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: \'FAQ\'s\'', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
					Repeater::make( _x( 'FAQ items', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_ITEMS )
						->fields(
							array(
								Text::make( _x( 'Question', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE )
									->required(),
								WYSIWYGEditor::make( _x( 'Answer', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_CONTENT )
									->toolbar( 'basic' )
									->disableMediaUpload()
									->required(),
							)
						)
						->minRows( 1 )
						->layout( 'block' )
						->required(),
					TrueFalse::make( _x( 'Open first FAQ item', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_EXPAND_FIRST_ITEM )
						->stylized(),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
