<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WysiwygEditor;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class CTA {
	public const BLOCK_NAME = 'cta';

	protected const FIELD_GROUP = 'block_cta';

	public const BLOCK_META_USAGE   = 'usage';
	public const BLOCK_META_GROUP   = 'cta';
	public const BLOCK_META_TITLE   = 'title';
	public const BLOCK_META_IMAGE   = 'image';
	public const BLOCK_META_CONTENT = 'content';
	public const BLOCK_META_BUTTON  = 'button';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Call to Action', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a call to action', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'megaphone',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'call to action', 'cta' ),
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
				'title'    => _x( 'Call to action', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Message::make( _x( 'Usage', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_USAGE )
						->message( _x( 'Add a call to action. Image, title, text and link are required fields.', 'Fields', 'fdmg-labels-admin' ) )
						->newLines( 'wpautop' ),
					Group::make( _x( 'CTA', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_GROUP )
					->fields(
						array(
							Text::make( _x( 'Title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE )
								->required(),
							Image::make( _x( 'Image', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_IMAGE )
								->required()
								->returnFormat( 'id' )
								->previewSize( 'medium' )
								->library( 'all' ),
							WysiwygEditor::make( _x( 'Content', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_CONTENT )
								->required()
								->tabs( 'visual' )
								->toolbar( 'basic' )
								->mediaUpload( false ),
							Link::make( _x( 'Button', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_BUTTON )
								->required()
								->returnFormat( 'array' ),
						),
					),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
