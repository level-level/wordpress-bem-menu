<?php
namespace Clarkson\Theme\ACFWidgets;

use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class ArticlesWithMostComments {
	public const WIDGET_NAME = 'articles-with-most-comments';

	protected const FIELD_GROUP = 'widget_articles_with_most_comments';

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
				'title'       => _x( 'Articles with most comments', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays an article block sorted by articles with the most comments. This block will automatically show the articles.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'admin-comments',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'comments', 'reacties', 'artikelen', 'posts', 'articles' ),
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
				'title'    => _x( 'Latest articles', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this field to overwrite the default "Articles with the most comments" title', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_TITLE ),
					Number::make( _x( 'Overwrite the default amount (5) of articles to be shown.', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_AMOUNT )
						->defaultValue( 5 )
						->min( 1 )
						->step( 1 )
						->append( _x( 'articles', 'Fields', 'fdmg-labels-admin' ) ),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
