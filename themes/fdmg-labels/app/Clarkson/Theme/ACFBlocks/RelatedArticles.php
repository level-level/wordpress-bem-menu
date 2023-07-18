<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Post;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class RelatedArticles {
	public const BLOCK_NAME = 'related-articles';

	protected const FIELD_GROUP = 'block_related_articles';

	public const BLOCK_META_USAGE            = 'usage';
	public const BLOCK_META_TITLE            = 'title';
	public const BLOCK_META_SELECTED_POSTS   = 'selected_posts';
	public const BLOCK_META_IS_FILL_DISABLED = 'is_fill_disabled';
	public const BLOCK_META_TERMS            = 'terms';
	public const BLOCK_META_AMOUNT           = 'amount';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Related articles', 'Block', 'fdmg-labels' ),
				'description' => _x( 'Displays a list of related articles based on multiple options.', 'Block', 'fdmg-labels' ),
				'icon'        => 'format-aside',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'related', 'articles', 'news', 'posts' ),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => false,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'  => base_block::PREVIEW_MODE_DATA,
							'is_fill_disabled' => false,
							'amount'           => 2,
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
				'title'    => _x( 'Articles in columns', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Message::make( _x( 'Usage', 'Fields', 'fdmg-labels' ), self::BLOCK_META_USAGE )
						->message( _x( 'Displays a list of articles in a two (2) or three (3) column layout based on multiple options.', 'Fields', 'fdmg-labels' ) )
						->newLines( 'wpautop' ),
					Text::make( _x( 'Use this title to overwrite the default title: \'Related news\'', 'Fields', 'fdmg-labels' ), self::BLOCK_META_TITLE ),
					Relationship::make( _x( 'Select articles to be sticky (optional)', 'Fields', 'fdmg-labels' ), self::BLOCK_META_SELECTED_POSTS )
						->postTypes( array( Post::POST_TYPE_NAME ) )
						->filters(
							array(
								'search',
								'taxonomy',
							)
						)
						->elements(
							array(
								'featured_image',
							)
						)
						->returnFormat( 'object' ),
					TrueFalse::make( _x( 'Disallow this block to automatically fill with the latest articles', 'Fields', 'fdmg-labels' ), self::BLOCK_META_IS_FILL_DISABLED )
						->stylisedUi( _x( 'Yes', 'Fields', 'fdmg-labels' ), _x( 'No', 'Fields', 'fdmg-labels' ) ),
					Taxonomy::make( _x( 'Select one or more categories to show articles from (optional)', 'Fields', 'fdmg-labels' ), self::BLOCK_META_TERMS )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::BLOCK_META_IS_FILL_DISABLED, '!=', '1' ),
							)
						)
						->taxonomy( 'category' )
						->appearance( 'multi_select' )
						->allowNull()
						->addTerm( false )
						->saveTerms( false )
						->loadTerms( false )
						->returnFormat( 'object' ),
					Number::make( _x( 'Select the maximum amount of articles to be shown', 'Fields', 'fdmg-labels' ), self::BLOCK_META_AMOUNT )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::BLOCK_META_IS_FILL_DISABLED, '!=', '1' ),
							)
						)
						->defaultValue( 3 )
						->min( 1 )
						->step( 1 )
						->append( _x( 'Articles', 'Fields', 'fdmg-labels' ) )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
