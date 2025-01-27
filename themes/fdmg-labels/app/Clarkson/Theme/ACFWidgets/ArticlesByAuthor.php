<?php
namespace Clarkson\Theme\ACFWidgets;

use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class ArticlesByAuthor {
	public const WIDGET_NAME = 'articles-by-author';

	protected const FIELD_GROUP = 'widget_articles_by_author';

	public const WIDGET_META_AMOUNT = 'amount';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Articles by author', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of articles by the author of the current post that is viewed.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'groups',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'articles by author', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'author', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
							'amount'          => 4,
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
				'title'    => _x( 'Articles by author', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Number::make( _x( 'Overwrite the default amount (4) of articles to be shown.', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_AMOUNT )
						->default( 4 )
						->min( 1 )
						->max( 20 )
						->step( 1 )
						->suffix( _x( 'articles', 'Fields', 'fdmg-labels-admin' ) ),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
