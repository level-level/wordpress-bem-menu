<?php
namespace Clarkson\Theme\PostTypes;

class Vacancy {
	public const POST_TYPE_NAME = 'ii_vacancy';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'                  => _x( 'Vacancies', 'Post Type', 'impact-investor' ),
				'singular_name'         => _x( 'Vacancy', 'Post Type', 'impact-investor' ),
				'all_items'             => _x( 'All vacancies', 'Post Type', 'impact-investor' ),
				'archives'              => _x( 'Vacancy archives', 'Post Type', 'impact-investor' ),
				'attributes'            => _x( 'Vacancy attributes', 'Post Type', 'impact-investor' ),
				'insert_into_item'      => _x( 'Insert into vacancy', 'Post Type', 'impact-investor' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this vacancy', 'Post Type', 'impact-investor' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'impact-investor' ),
				'filter_items_list'     => _x( 'Filter vacancies list', 'Post Type', 'impact-investor' ),
				'items_list_navigation' => _x( 'Vacancies list navigation', 'Post Type', 'impact-investor' ),
				'items_list'            => _x( 'Vacancies list', 'Post Type', 'impact-investor' ),
				'new_item'              => _x( 'New vacancy', 'Post Type', 'impact-investor' ),
				'add_new'               => _x( 'New vacancy', 'Post Type', 'impact-investor' ),
				'add_new_item'          => _x( 'Add new vacancy', 'Post Type', 'impact-investor' ),
				'edit_item'             => _x( 'Edit vacancy', 'Post Type', 'impact-investor' ),
				'view_item'             => _x( 'View vacancy', 'Post Type', 'impact-investor' ),
				'view_items'            => _x( 'View vacancies', 'Post Type', 'impact-investor' ),
				'search_items'          => _x( 'Search vacancies', 'Post Type', 'impact-investor' ),
				'not_found'             => _x( 'No vacancies found', 'Post Type', 'impact-investor' ),
				'not_found_in_trash'    => _x( 'No vacancies found in trash', 'Post Type', 'impact-investor' ),
				'parent_item_colon'     => _x( 'Parent vacancy:', 'Post Type', 'impact-investor' ),
				'menu_name'             => _x( 'Vacancies', 'Post Type', 'impact-investor' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'vacancies', 'impact-investor-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-money',
			'show_in_rest'          => true,
			'rest_base'             => self::POST_TYPE_NAME,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}
}
