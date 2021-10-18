<?php

namespace Clarkson\Theme\Taxonomies;

class ExampleTax {

	public const TAXONOMY_NAME = 'example_tax';
	protected const POST_TYPES = array( 'example' );

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		$args = array(
			'hierarchical'          => true,
			'public'                => true,
			'show_in_nav_menus'     => true,
			'show_ui'               => true,
			'show_admin_column'     => true,
			'query_var'             => true,
			'rewrite'               => true,
			'capabilities'          => array(
				'manage_terms' => 'edit_posts',
				'edit_terms'   => 'edit_posts',
				'delete_terms' => 'edit_posts',
				'assign_terms' => 'edit_posts',
			),
			'labels'                => array(
				'name'                       => _x( 'Examples', 'Taxonomy', 'impact-investor' ),
				'singular_name'              => _x( 'Example', 'Taxonomy', 'impact-investor' ),
				'search_items'               => _x( 'Search Examples', 'Taxonomy', 'impact-investor' ),
				'popular_items'              => _x( 'Popular Examples', 'Taxonomy', 'impact-investor' ),
				'all_items'                  => _x( 'All Examples', 'Taxonomy', 'impact-investor' ),
				'parent_item'                => _x( 'Parent Example', 'Taxonomy', 'impact-investor' ),
				'parent_item_colon'          => _x( 'Parent Example:', 'Taxonomy', 'impact-investor' ),
				'edit_item'                  => _x( 'Edit Example', 'Taxonomy', 'impact-investor' ),
				'update_item'                => _x( 'Update Example', 'Taxonomy', 'impact-investor' ),
				'view_item'                  => _x( 'View Example', 'Taxonomy', 'impact-investor' ),
				'add_new_item'               => _x( 'Add New Example', 'Taxonomy', 'impact-investor' ),
				'new_item_name'              => _x( 'New Example', 'Taxonomy', 'impact-investor' ),
				'separate_items_with_commas' => _x( 'Separate Examples with commas', 'Taxonomy', 'impact-investor' ),
				'add_or_remove_items'        => _x( 'Add or remove Examples', 'Taxonomy', 'impact-investor' ),
				'choose_from_most_used'      => _x( 'Choose from the most used Examples', 'Taxonomy', 'impact-investor' ),
				'not_found'                  => _x( 'No Examples found.', 'Taxonomy', 'impact-investor' ),
				'no_terms'                   => _x( 'No Examples', 'Taxonomy', 'impact-investor' ),
				'menu_name'                  => _x( 'Examples', 'Taxonomy', 'impact-investor' ),
				'items_list_navigation'      => _x( 'Examples list navigation', 'Taxonomy', 'impact-investor' ),
				'items_list'                 => _x( 'Examples list', 'Taxonomy', 'impact-investor' ),
				'most_used'                  => _x( 'Most Used', 'Taxonomy', 'impact-investor' ),
				'back_to_items'              => _x( '&larr; Back to Examples', 'Taxonomy', 'impact-investor' ),
			),
			'show_in_rest'          => true,
			'rest_controller_class' => 'WP_REST_Terms_Controller',
		);
		register_taxonomy( self::TAXONOMY_NAME, self::POST_TYPES, $args );

		foreach ( self::POST_TYPES as $post_type ) {
			register_taxonomy_for_object_type( self::TAXONOMY_NAME, $post_type );
		}
	}
}
