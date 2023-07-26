<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Post;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class ArticlesInColumns {
	public const BLOCK_NAME = 'articles-in-columns';

	protected const FIELD_GROUP = 'block_articles_in_columns';

	public const BLOCK_META_USAGE            = 'usage';
	public const BLOCK_META_COLUMNS          = 'columns';
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
				'title'       => _x( 'Articles in columns', 'Block', 'fdmg-labels' ),
				'description' => _x( 'Displays a list of articles in a one (1), two (2) or three (3) column layout based on multiple options.', 'Block', 'fdmg-labels' ),
				'icon'        => 'columns',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'posts', 'articles', 'column', 'two', '1', '2', 'three', '3', 'post', 'nieuws', 'artikel', 'news' ),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
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
						->message( _x( 'Displays a list of articles in a one (1), two (2) or three (3) column layout based on multiple options.', 'Fields', 'fdmg-labels' ) )
						->newLines( 'wpautop' ),
					Select::make( _x( 'Select amount of columns', 'Fields', 'fdmg-labels' ), self::BLOCK_META_COLUMNS )
						->choices(
							array(
								1 => _x( 'One (1) column', 'Fields', 'fdmg-labels' ),
								2 => _x( 'Two (2) columns', 'Fields', 'fdmg-labels' ),
								3 => _x( 'Three (3) columns', 'Fields', 'fdmg-labels' ),
							)
						)
						->defaultValue( 2 )
						->returnFormat( 'value' )
						->required(),
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
						->defaultValue( 2 )
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
