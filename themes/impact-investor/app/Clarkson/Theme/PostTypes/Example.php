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
				'name'                  => _x( 'Examples', 'Post Type', 'impact-investor' ),
				'singular_name'         => _x( 'Example', 'Post Type', 'impact-investor' ),
				'all_items'             => _x( 'All Examples', 'Post Type', 'impact-investor' ),
				'archives'              => _x( 'Example Archives', 'Post Type', 'impact-investor' ),
				'attributes'            => _x( 'Example Attributes', 'Post Type', 'impact-investor' ),
				'insert_into_item'      => _x( 'Insert into Example', 'Post Type', 'impact-investor' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this Example', 'Post Type', 'impact-investor' ),
				'featured_image'        => _x( 'Featured Image', 'Post Type', 'impact-investor' ),
				'filter_items_list'     => _x( 'Filter Examples list', 'Post Type', 'impact-investor' ),
				'items_list_navigation' => _x( 'Examples list navigation', 'Post Type', 'impact-investor' ),
				'items_list'            => _x( 'Examples list', 'Post Type', 'impact-investor' ),
				'new_item'              => _x( 'New Example', 'Post Type', 'impact-investor' ),
				'add_new'               => _x( 'New Example', 'Post Type', 'impact-investor' ),
				'add_new_item'          => _x( 'Add New Example', 'Post Type', 'impact-investor' ),
				'edit_item'             => _x( 'Edit Example', 'Post Type', 'impact-investor' ),
				'view_item'             => _x( 'View Example', 'Post Type', 'impact-investor' ),
				'view_items'            => _x( 'View Examples', 'Post Type', 'impact-investor' ),
				'search_items'          => _x( 'Search Examples', 'Post Type', 'impact-investor' ),
				'not_found'             => _x( 'No Examples found', 'Post Type', 'impact-investor' ),
				'not_found_in_trash'    => _x( 'No Examples found in trash', 'Post Type', 'impact-investor' ),
				'parent_item_colon'     => _x( 'Parent Example:', 'Post Type', 'impact-investor' ),
				'menu_name'             => _x( 'Examples', 'Post Type', 'impact-investor' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'examples', 'impact-investor-slugs' ) ),
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
