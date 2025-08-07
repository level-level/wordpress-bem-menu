<?php

namespace Clarkson\Theme\Taxonomies;

use Clarkson\Theme\PostTypes\Post;
use FDMGLabels\FeatureManager;

class Genre {

	public const TAXONOMY_NAME = 'fdmg_genre';
	protected const POST_TYPES = array( Post::POST_TYPE_NAME );

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ), 4 );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		$enabled = ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY );

		$args = array(
			'hierarchical'          => true,
			'public'                => $enabled,
			'show_in_nav_menus'     => $enabled,
			'show_ui'               => $enabled,
			'show_admin_column'     => $enabled,
			'query_var'             => true,
			'rewrite'               => array( 'slug' => __( 'genre', 'fdmg-labels-slugs' ) ),
			'capabilities'          => array(
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
			),
			'labels'                => array(
				'name'                       => _x( 'Genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'singular_name'              => _x( 'Genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'search_items'               => _x( 'Search genres', 'Taxonomy', 'fdmg-labels-admin' ),
				'all_items'                  => _x( 'All genres', 'Taxonomy', 'fdmg-labels-admin' ),
				'parent_item'                => _x( 'Parent genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'parent_item_colon'          => _x( 'Parent genre:', 'Taxonomy', 'fdmg-labels-admin' ),
				'edit_item'                  => _x( 'Edit genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'update_item'                => _x( 'Update genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'view_item'                  => _x( 'View genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'add_new_item'               => _x( 'Add new genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'new_item_name'              => _x( 'New genre', 'Taxonomy', 'fdmg-labels-admin' ),
				'separate_items_with_commas' => _x( 'Seperate genres with comma\'s', 'Taxonomy', 'fdmg-labels-admin' ),
				'add_or_remove_items'        => _x( 'Add or remove genres', 'Taxonomy', 'fdmg-labels-admin' ),
				'choose_from_most_used'      => _x( 'Choose from most used genres', 'Taxonomy', 'fdmg-labels-admin' ),
				'not_found'                  => _x( 'No genres found', 'Taxonomy', 'fdmg-labels-admin' ),
				'no_terms'                   => _x( 'No genres', 'Taxonomy', 'fdmg-labels-admin' ),
				'back_to_items'              => _x( '&larr; Back', 'Taxonomy', 'fdmg-labels-admin' ),
			),
			'show_in_rest'          => $enabled,
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy( self::TAXONOMY_NAME, self::POST_TYPES, $args );

		foreach ( self::POST_TYPES as $post_type ) {
			register_taxonomy_for_object_type( self::TAXONOMY_NAME, $post_type );
		}
	}

	public function register_fields(): void {}
}
