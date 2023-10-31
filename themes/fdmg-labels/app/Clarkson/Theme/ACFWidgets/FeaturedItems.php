<?php
namespace Clarkson\Theme\ACFWidgets;

use Clarkson\Theme\PostTypes\Post;
use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;
use Gutenberg\Blocks\base_block;

class FeaturedItems {
	public const BLOCK_NAME = 'featured-items';

	protected const FIELD_GROUP = 'widget_featured_items';

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
				'name'        => 'widget-' . self::BLOCK_NAME,
				'title'       => _x( 'Featured items', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays featured items.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'welcome-view-site',
				'keywords'    => array(
					_x( 'featured articles', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'articles', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => false,
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'       => base_block::PREVIEW_MODE_DATA,
							self::BLOCK_META_IS_FILL_ENABLED => true,
							self::BLOCK_META_AMOUNT => 3,
						),
					),
				),
			),
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
			Relationship::make( _x( 'Select featured articles to be sticky (optional)', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SELECTED_POSTS )
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
			TrueFalse::make( _x( 'Allow this block to automatically fill', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_IS_FILL_ENABLED )
				->defaultValue( true )
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
				->defaultValue( 2 )
				->min( 1 )
				->max( 30 )
				->step( 1 )
				->append( _x( 'Articles', 'Fields', 'fdmg-labels-admin' ) )
				->required(),
		);

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Featured articles', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array_merge( $fields, $taxonomy_fields, $fields_after ),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
