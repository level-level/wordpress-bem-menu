<?php
namespace Clarkson\Theme\ACFPages;

use Clarkson\Theme\PostTypes\Team;
use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class SearchOptions {

	protected const FIELD_GROUP = 'search-result-options';

	public const MENU_SLUG                           = 'acf-search-result-options-options';
	public const META_SEARCH_RESULTS                 = 'search_results';
	public const META_SEARCH_TAB_FILTERS             = 'search_tab_filters';
	public const META_SEARCH_TAB_OPTIONS             = 'search_tab_options';
	public const META_SEARCH_RESULTS_GENRES          = 'search_results_genres';
	public const META_SEARCH_RESULTS_SECTIONS        = 'search_results_sections';
	public const META_SEARCH_RESULTS_CATEGORIES      = 'search_results_categories';
	public const META_SEARCH_RESULTS_AUTHORS         = 'search_results_authors';
	public const META_SEARCH_RESULTS_AUTHOR_AMOUNT   = 'search_results_author_amount';
	public const META_SEARCH_RESULTS_CATEGORY_AMOUNT = 'search_results_category_amount';
	public const META_SEARCH_RESULTS_GENRE_AMOUNT    = 'search_results_genre_amount';
	public const META_SEARCH_RESULTS_SECTION_AMOUNT  = 'search_results_section_amount';
	public const META_SEARCH_DEFAULT_SORTING         = 'search_default_sorting';
	public const META_SEARCH_DEFAULT_MIN_POST_DATE   = 'search_default_min_post_date';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	/**
	 *
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		if ( is_search() ) {
			$context['search_options'] = $this;
			$context['search']         = get_field( self::META_SEARCH_RESULTS, 'options' ) ?: array();
		}

		return $context;
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Search result options', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'Search result options', 'Options', 'fdmg-labels-admin' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => ThemeOptions::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
			)
		);
	}

	public function register_fields(): void {

		// Conditional fields
		$taxonomy_fields = array();

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select the genres to use as a filter.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_GENRES )
				->taxonomy( Genre::TAXONOMY_NAME )
				->appearance( 'multi_select' )
				->nullable()
				->create( false )
				->save( false )
				->load( false )
				->format( 'object' );

			$taxonomy_fields[] = Number::make( _x( 'How many genres must be shown by default before a "Show more" button appears?', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_GENRE_AMOUNT )
				->default( 5 )
				->min( 3 );
		}

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select the sections to use as a filter.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_SECTIONS )
			->taxonomy( Section::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->nullable()
			->create( false )
			->save( false )
			->load( false )
			->format( 'object' );

			$taxonomy_fields[] = Number::make( _x( 'How many sections must be shown by default before a "Show more" button appears?', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_SECTION_AMOUNT )
			->default( 5 )
			->min( 3 );
		}

		if ( ! ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select the categories to use as a filter.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_CATEGORIES )
			->taxonomy( Category::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->nullable()
			->create( false )
			->save( false )
			->load( false )
			->format( 'object' );

			$taxonomy_fields[] = Number::make( _x( 'How many categories must be shown by default before a "Show more" button appears?', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_CATEGORY_AMOUNT )
			->default( 5 )
			->min( 3 );
		}

		// Default fields
		$fields = array(
			Tab::make( _x( 'Filters', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_TAB_FILTERS )
				->placement( 'left' ),
			Message::make( _x( 'Filters on the search results page', 'Fields', 'fdmg-labels-admin' ) )
				->body( _x( 'Define what filters to be used on the search results page.', 'Fields', 'fdmg-labels-admin' ) ),
			Relationship::make( _x( 'Select the authors to show as a filter option on the search results page.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_AUTHORS )
				->postTypes(
					array(
						Team::POST_TYPE_NAME,
					)
				)
				->filters(
					array(
						'search',
					)
				),
			Number::make( _x( 'How many authors must be shown by default before a "Show more" button appears?', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_AUTHOR_AMOUNT )
				->default( 5 )
				->min( 3 ),
		);

		$option_fields = array(
			Tab::make( _x( 'Options', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_TAB_OPTIONS )
				->placement( 'left' ),
			Message::make( _x( 'Search result options', 'Fields', 'fdmg-labels-admin' ) )
				->body( _x( 'Select the default sorting and minimum post date when a user does a new search query.', 'Fields', 'fdmg-labels-admin' ) ),
			Select::make( _x( 'Default sorting', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_DEFAULT_SORTING )
				->choices(
					array(
						'relevance' => _x( 'Relevance', 'Fields', 'fdmg-labels-admin' ),
						'date'      => _x( 'Date', 'Fields', 'fdmg-labels-admin' ),
					)
				)
				->default( 'relevance' ),
			Select::make( _x( 'Default period', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_DEFAULT_MIN_POST_DATE )
				->choices(
					array(
						'week'     => _x( 'Week', 'Fields', 'fdmg-labels-admin' ),
						'month'    => _x( 'Month', 'Fields', 'fdmg-labels-admin' ),
						'year'     => _x( 'Year', 'Fields', 'fdmg-labels-admin' ),
						'3-years'  => _x( '3 years', 'Fields', 'fdmg-labels-admin' ),
						'2000-now' => _x( '2000 - now', 'Fields', 'fdmg-labels-admin' ),
					)
				)
				->default( '3-years' ),
		);

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Search result options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Search result options', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS )
					->fields(
						array_merge( $fields, $taxonomy_fields, $option_fields ),
					),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
