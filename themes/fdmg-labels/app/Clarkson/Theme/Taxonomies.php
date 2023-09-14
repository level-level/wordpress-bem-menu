<?php
namespace Clarkson\Theme;

use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Clarkson\Theme\Taxonomies\Serie;
use FDMGLabels\FeatureManager;

class Taxonomies {

	public const TAXONOMIES = array(
		Category::class,
		Genre::class,
		Section::class,
		Serie::class,
	);

	public function register_hooks(): void {
		foreach ( self::TAXONOMIES as $taxonomy_class ) {
			( new $taxonomy_class() )->register_hooks();
		}

		add_action( 'init', array( $this, 'unregister_taxonomy' ) );
	}

	public function unregister_taxonomy(): void {
		register_taxonomy( 'post_tag', array() );

		/**
		 * If feature flag: 'remove' the category taxonomy by registering it with an empty array of post types.
		 */
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
			register_taxonomy( Category::TAXONOMY_NAME, array() );
		}
	}
}
