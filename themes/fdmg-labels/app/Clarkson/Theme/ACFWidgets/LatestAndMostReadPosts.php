<?php
namespace Clarkson\Theme\ACFWidgets;

use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class LatestAndMostReadPosts {
	public const WIDGET_NAME = 'latest-and-most-read-posts';

	protected const FIELD_GROUP = 'widget_latest_and_most_read_posts';

	public const BLOCK_META_LATEST_TITLE               = 'latest_posts_title';
	public const BLOCK_META_LATEST_BUTTON_TITLE        = 'latest_posts_button_title';
	public const BLOCK_META_LATEST_AMOUNT              = 'latest_posts_amount';
	public const BLOCK_META_MOST_READ_TITLE            = 'most_read_posts_title';
	public const BLOCK_META_MOST_READ_AMOUNT           = 'most_read_posts_amount';
	public const BLOCK_META_SHOW_LATEST_ARTICLES_FIRST = 'show_latest_articles_first';
	public const BLOCK_META_SHOW_ARTICLE_TERMS         = 'show_article_terms';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Latest and most read posts', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of latest and most read posts in a tabbed component.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'star-filled',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'latest articles', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'most read', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'populair', 'Keyword', 'fdmg-labels-admin' ),
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
				'title'    => _x( 'Latest and most read posts', 'Block', 'fdmg-labels-admin' ),
				'fields'   => array(
					Tab::make( _x( 'Latest articles', 'Fields', 'fdmg-labels-admin' ) ),
					Text::make( _x( 'Use this field to overwrite the default "Latest articles" title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_LATEST_TITLE ),
					Number::make( _x( 'Overwrite the default amount (5) of the latest articles to be shown.', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_LATEST_AMOUNT )
						->default( 5 )
						->min( 1 )
						->step( 1 )
						->suffix( _x( 'articles', 'Fields', 'fdmg-labels-admin' ) ),
					Text::make( _x( 'Use this field to overwrite the default button title "More articles"', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_LATEST_BUTTON_TITLE ),
					TrueFalse::make( _x( 'Display the terms of an article', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SHOW_ARTICLE_TERMS )
						->stylized()
						->default( true ),
					Tab::make( _x( 'Most read', 'Fields', 'fdmg-labels-admin' ) ),
					Text::make( _x( 'Use this field to overwrite the default "Most read" title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_MOST_READ_TITLE ),
					Number::make( _x( 'Overwrite the default amount (5) of the most read articles to be shown.', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_MOST_READ_AMOUNT )
						->default( 5 )
						->min( 1 )
						->step( 1 )
						->suffix( _x( 'articles', 'Fields', 'fdmg-labels-admin' ) ),
					TrueFalse::make( _x( 'Display the "Latest articles" tab first', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SHOW_LATEST_ARTICLES_FIRST )
						->stylized()
						->default( false ),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
