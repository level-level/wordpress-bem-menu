<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson_Core\WordPress_Object\dlm_download;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Downloads {
	public const BLOCK_NAME = 'downloads';

	protected const FIELD_GROUP = 'block_downloadss';

	public const BLOCK_META_GROUP     = 'downloads';
	public const BLOCK_META_TITLE     = 'title';
	public const BLOCK_META_DOWNLOADS = 'downloads';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Downloads', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Offer the reader downloadable files such as a PDF with this block. First add it yourself under Downloads in the WordPress menu. Then you can select it below.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'download',
				'align'       => 'right', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'download monitor', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'file', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'pdf', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'attachment', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center', 'left', 'right' ),
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
				'title'    => _x( 'Downloads', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Downloads', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_GROUP )
					->fields(
						array(
							Text::make( _x( 'Title of the downloads block', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),

							Relationship::make( _x( 'Select downloads', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_DOWNLOADS )
								->required()
								->postTypes( array( dlm_download::$type ) )
								->filters(
									array(
										'search',
										'taxonomy',
									)
								),
						)
					),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
