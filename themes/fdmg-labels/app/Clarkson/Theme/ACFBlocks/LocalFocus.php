<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class LocalFocus {
	public const BLOCK_NAME = 'localfocus';

	protected const FIELD_GROUP = 'block_localfocus';

	public const BLOCK_META_TITLE    = 'title';
	public const BLOCK_META_EMBED_ID = 'embed_id';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'LocalFocus embed', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a embed from LocalFocus.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'embed-post',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'localfocus', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'embed', 'Keyword', 'fdmg-labels-admin' ),
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
				'title'    => _x( 'LocalFocus embed', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE )
						->instructions( _x( 'For accessibility, it is important that the embed has a clear title. Only visible for screen readers.', 'Fields', 'fdmg-labels-admin' ) )
						->required(),

					Text::make( _x( 'Embed ID', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_EMBED_ID )
						->instructions( _x( 'The ID of the embed, for example: 650c463f26fb0', 'Fields', 'fdmg-labels-admin' ) )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
