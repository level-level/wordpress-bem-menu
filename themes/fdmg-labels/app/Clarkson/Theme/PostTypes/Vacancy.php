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
				'name'                  => _x( 'Vacancies', 'Post Type', 'fdmg-labels' ),
				'singular_name'         => _x( 'Vacancy', 'Post Type', 'fdmg-labels' ),
				'all_items'             => _x( 'All vacancies', 'Post Type', 'fdmg-labels' ),
				'archives'              => _x( 'Vacancy archives', 'Post Type', 'fdmg-labels' ),
				'attributes'            => _x( 'Vacancy attributes', 'Post Type', 'fdmg-labels' ),
				'insert_into_item'      => _x( 'Insert into vacancy', 'Post Type', 'fdmg-labels' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this vacancy', 'Post Type', 'fdmg-labels' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'fdmg-labels' ),
				'filter_items_list'     => _x( 'Filter vacancies list', 'Post Type', 'fdmg-labels' ),
				'items_list_navigation' => _x( 'Vacancies list navigation', 'Post Type', 'fdmg-labels' ),
				'items_list'            => _x( 'Vacancies list', 'Post Type', 'fdmg-labels' ),
				'new_item'              => _x( 'New vacancy', 'Post Type', 'fdmg-labels' ),
				'add_new'               => _x( 'New vacancy', 'Post Type', 'fdmg-labels' ),
				'add_new_item'          => _x( 'Add new vacancy', 'Post Type', 'fdmg-labels' ),
				'edit_item'             => _x( 'Edit vacancy', 'Post Type', 'fdmg-labels' ),
				'view_item'             => _x( 'View vacancy', 'Post Type', 'fdmg-labels' ),
				'view_items'            => _x( 'View vacancies', 'Post Type', 'fdmg-labels' ),
				'search_items'          => _x( 'Search vacancies', 'Post Type', 'fdmg-labels' ),
				'not_found'             => _x( 'No vacancies found', 'Post Type', 'fdmg-labels' ),
				'not_found_in_trash'    => _x( 'No vacancies found in trash', 'Post Type', 'fdmg-labels' ),
				'parent_item_colon'     => _x( 'Parent vacancy:', 'Post Type', 'fdmg-labels' ),
				'menu_name'             => _x( 'Vacancies', 'Post Type', 'fdmg-labels' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => false,
			'rewrite'               => array( 'slug' => __( 'vacancies', 'fdmg-labels-slugs' ) ),
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
