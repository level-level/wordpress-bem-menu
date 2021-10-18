<?php
namespace Clarkson\Theme\PostTypes;

class Example {
	public const POST_TYPE_NAME = 'example';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'                  => _x( 'Examples', 'Post Type', 'clarkson-theme-dawn' ),
				'singular_name'         => _x( 'Example', 'Post Type', 'clarkson-theme-dawn' ),
				'all_items'             => _x( 'All Examples', 'Post Type', 'clarkson-theme-dawn' ),
				'archives'              => _x( 'Example Archives', 'Post Type', 'clarkson-theme-dawn' ),
				'attributes'            => _x( 'Example Attributes', 'Post Type', 'clarkson-theme-dawn' ),
				'insert_into_item'      => _x( 'Insert into Example', 'Post Type', 'clarkson-theme-dawn' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Example', 'Post Type', 'clarkson-theme-dawn' ),
				'featured_image'        => _x( 'Featured Image', 'Post Type', 'clarkson-theme-dawn' ),
				'filter_items_list'     => _x( 'Filter Examples list', 'Post Type', 'clarkson-theme-dawn' ),
				'items_list_navigation' => _x( 'Examples list navigation', 'Post Type', 'clarkson-theme-dawn' ),
				'items_list'            => _x( 'Examples list', 'Post Type', 'clarkson-theme-dawn' ),
				'new_item'              => _x( 'New Example', 'Post Type', 'clarkson-theme-dawn' ),
				'add_new'               => _x( 'New Example', 'Post Type', 'clarkson-theme-dawn' ),
				'add_new_item'          => _x( 'Add New Example', 'Post Type', 'clarkson-theme-dawn' ),
				'edit_item'             => _x( 'Edit Example', 'Post Type', 'clarkson-theme-dawn' ),
				'view_item'             => _x( 'View Example', 'Post Type', 'clarkson-theme-dawn' ),
				'view_items'            => _x( 'View Examples', 'Post Type', 'clarkson-theme-dawn' ),
				'search_items'          => _x( 'Search Examples', 'Post Type', 'clarkson-theme-dawn' ),
				'not_found'             => _x( 'No Examples found', 'Post Type', 'clarkson-theme-dawn' ),
				'not_found_in_trash'    => _x( 'No Examples found in trash', 'Post Type', 'clarkson-theme-dawn' ),
				'parent_item_colon'     => _x( 'Parent Example:', 'Post Type', 'clarkson-theme-dawn' ),
				'menu_name'             => _x( 'Examples', 'Post Type', 'clarkson-theme-dawn' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'examples', 'clarkson-theme-dawn-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-admin-post',
			'show_in_rest'          => true,
			'rest_base'             => self::POST_TYPE_NAME,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}
}
