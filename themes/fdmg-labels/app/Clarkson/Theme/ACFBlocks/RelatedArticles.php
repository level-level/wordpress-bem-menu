<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Post;
use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;
use Gutenberg\Blocks\base_block;

class RelatedArticles {
	public const BLOCK_NAME = 'related-articles';

	protected const FIELD_GROUP = 'block_related_articles';

	public const BLOCK_META_USAGE           = 'usage';
	public const BLOCK_META_TITLE           = 'title';
	public const BLOCK_META_SELECTED_POSTS  = 'selected_posts';
	public const BLOCK_META_IS_FILL_ENABLED = 'is_fill_enabled';
	public const BLOCK_META_TERMS           = 'terms';
	public const BLOCK_META_GENRES          = 'genres';
	public const BLOCK_META_SECTIONS        = 'sections';
	public const BLOCK_META_AMOUNT          = 'amount';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Related articles', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of related articles based on multiple options.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'format-aside',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'related', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'articles', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'news', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => false,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'       => base_block::PREVIEW_MODE_DATA,
							self::BLOCK_META_IS_FILL_ENABLED => true,
							self::BLOCK_META_AMOUNT => 2,
						),
					),
				),
			)
		);
	}

	public function register_fields(): void {

		// Conditional fields
		$taxonomy_fields = array();

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more genres to show articles from (optional)', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_GENRES )
			->conditionalLogic(
				array(
					ConditionalLogic::where( self::BLOCK_META_IS_FILL_ENABLED, '==', '1' ),
				)
			)
			->taxonomy( Genre::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false )
			->returnFormat( 'object' );
		}

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more sections to show articles from (optional)', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SECTIONS )
			->conditionalLogic(
				array(
					ConditionalLogic::where( self::BLOCK_META_IS_FILL_ENABLED, '==', '1' ),
				)
			)
			->taxonomy( Section::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false )
			->returnFormat( 'object' );
		}

		if ( ! ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more categories to show articles from (optional)', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TERMS )
			->conditionalLogic(
				array(
					ConditionalLogic::where( self::BLOCK_META_IS_FILL_ENABLED, '==', '1' ),
				)
			)
			->taxonomy( Category::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false )
			->returnFormat( 'object' );
		}

		// Default fields
		$fields = array(
			Message::make( _x( 'Usage', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_USAGE )
				->message( _x( 'Displays a list of articles in a two (2) or three (3) column layout based on multiple options.', 'Fields', 'fdmg-labels-admin' ) )
				->newLines( 'wpautop' ),
			Text::make( _x( 'Use this title to overwrite the default title: \'Latest articles\'', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
			Relationship::make( _x( 'Select articles to be sticky (optional)', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SELECTED_POSTS )
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
			TrueFalse::make( _x( 'Allow this block to automatically fill with the latest articles', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_IS_FILL_ENABLED )
				// The default value is based on the feature flag.
				->defaultValue( ! ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_AUTOFILLING_RELATED_ARTICLES_BLOCK ) )
				->stylisedUi(),
		);

		// Fields after the conditional fields
		$fields_after = array(
			Number::make( _x( 'Select the maximum amount of articles to be shown', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_AMOUNT )
			->conditionalLogic(
				array(
					ConditionalLogic::where( self::BLOCK_META_IS_FILL_ENABLED, '==', '1' ),
				)
			)
			->defaultValue( 3 )
			->min( 1 )
			->max( 30 )
			->step( 1 )
			->append( _x( 'Articles', 'Fields', 'fdmg-labels-admin' ) )
			->required(),
		);

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Articles in columns', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array_merge( $fields, $taxonomy_fields, $fields_after ),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
