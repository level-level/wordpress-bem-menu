<?php

namespace Clarkson\Theme\Taxonomies;

use Clarkson\Theme\PostTypes\Podcast;
use FDMGLabels\FeatureManager;

class Serie {

	public const TAXONOMY_NAME = 'fdmg_serie';
	protected const POST_TYPES = array( Podcast::POST_TYPE_NAME );

	public function register_hooks(): void {
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_PODCAST_POST_TYPE ) ) {
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'acf/init', array( $this, 'register_fields' ) );
		}
	}

	public function register(): void {
		$args = array(
			'hierarchical'          => true,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => __( 'serie', 'fdmg-labels-slugs' ) ),
			'capabilities'          => array(
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
			),
			'labels'                => array(
				'name'                       => _x( 'Serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'singular_name'              => _x( 'Serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'search_items'               => _x( 'Search series', 'Taxonomy', 'fdmg-labels-admin' ),
				'all_items'                  => _x( 'All series', 'Taxonomy', 'fdmg-labels-admin' ),
				'parent_item'                => _x( 'Parent serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'parent_item_colon'          => _x( 'Parent serie:', 'Taxonomy', 'fdmg-labels-admin' ),
				'edit_item'                  => _x( 'Edit serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'update_item'                => _x( 'Update serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'view_item'                  => _x( 'View serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'add_new_item'               => _x( 'Add new serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'new_item_name'              => _x( 'New serie', 'Taxonomy', 'fdmg-labels-admin' ),
				'separate_items_with_commas' => _x( 'Seperate series with comma\'s', 'Taxonomy', 'fdmg-labels-admin' ),
				'add_or_remove_items'        => _x( 'Add or remove series', 'Taxonomy', 'fdmg-labels-admin' ),
				'choose_from_most_used'      => _x( 'Choose from most used series', 'Taxonomy', 'fdmg-labels-admin' ),
				'not_found'                  => _x( 'No series found', 'Taxonomy', 'fdmg-labels-admin' ),
				'no_terms'                   => _x( 'No series', 'Taxonomy', 'fdmg-labels-admin' ),
				'back_to_items'              => _x( '&larr; Back', 'Taxonomy', 'fdmg-labels-admin' ),
			),
			'show_in_rest'          => true,
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy( self::TAXONOMY_NAME, self::POST_TYPES, $args );

		foreach ( self::POST_TYPES as $post_type ) {
			register_taxonomy_for_object_type( self::TAXONOMY_NAME, $post_type );
		}
	}

	public function register_fields(): void {}
}
