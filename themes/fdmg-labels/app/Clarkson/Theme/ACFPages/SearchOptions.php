<?php
namespace Clarkson\Theme\ACFPages;

use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class SearchOptions {

	protected const FIELD_GROUP = 'search-result-options';

	public const MENU_SLUG                      = 'acf-search-result-options-options';
	public const META_SEARCH_RESULTS            = 'search_results';
	public const META_SEARCH_RESULTS_GENRES     = 'search_results_genres';
	public const META_SEARCH_RESULTS_SECTIONS   = 'search_results_sections';
	public const META_SEARCH_RESULTS_CATEGORIES = 'search_results_categories';

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
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false );
		}

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select the sections to use as a filter.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_SECTIONS )
			->taxonomy( Section::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false );
		}

		if ( ! ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
			$taxonomy_fields[] = Taxonomy::make( _x( 'Select the categories to use as a filter.', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS_CATEGORIES )
			->taxonomy( Category::TAXONOMY_NAME )
			->appearance( 'multi_select' )
			->allowNull()
			->addTerm( false )
			->saveTerms( false )
			->loadTerms( false );
		}

		// Default fields
		$fields = array(
			Message::make( _x( 'Filters on the search results page', 'Fields', 'fdmg-labels-admin' ) )
				->message( _x( 'Define what filters to be used on the search results page.', 'Fields', 'fdmg-labels-admin' ) ),
		);

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Search result options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Search result options', 'Fields', 'fdmg-labels-admin' ), self::META_SEARCH_RESULTS )
					->fields(
						array_merge( $fields, $taxonomy_fields ),
					),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
