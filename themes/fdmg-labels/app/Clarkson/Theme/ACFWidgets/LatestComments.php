<?php
namespace Clarkson\Theme\ACFWidgets;

use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class LatestComments {
	public const WIDGET_NAME = 'latest-comments';

	protected const FIELD_GROUP = 'widget_latest_comments';

	public const WIDGET_META_TITLE  = 'title';
	public const WIDGET_META_AMOUNT = 'amount';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Latest comments', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a block with the latest comments. This block will automatically show the comments.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'admin-comments',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'latest comments', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'amount'          => 5,
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
				'title'    => _x( 'Latest comments', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this field to overwrite the default "Latest comments" title', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_TITLE ),
					Number::make( _x( 'Overwrite the default amount (5) of comments to be shown.', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_AMOUNT )
						->default( 5 )
						->min( 1 )
						->step( 1 )
						->suffix( _x( 'comments', 'Fields', 'fdmg-labels-admin' ) ),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
