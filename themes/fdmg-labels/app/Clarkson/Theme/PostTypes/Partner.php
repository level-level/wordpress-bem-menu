<?php
namespace Clarkson\Theme\PostTypes;

class Partner {
	public const POST_TYPE_NAME = 'ii_partner';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'                  => _x( 'Partner', 'Post Type', 'impact-investor' ),
				'singular_name'         => _x( 'Partner', 'Post Type', 'impact-investor' ),
				'all_items'             => _x( 'All partners', 'Post Type', 'impact-investor' ),
				'archives'              => _x( 'Partner archives', 'Post Type', 'impact-investor' ),
				'attributes'            => _x( 'Partner attributes', 'Post Type', 'impact-investor' ),
				'insert_into_item'      => _x( 'Insert into partner', 'Post Type', 'impact-investor' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this partner', 'Post Type', 'impact-investor' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'impact-investor' ),
				'filter_items_list'     => _x( 'Filter partners list', 'Post Type', 'impact-investor' ),
				'items_list_navigation' => _x( 'Partner list navigation', 'Post Type', 'impact-investor' ),
				'items_list'            => _x( 'Partner list', 'Post Type', 'impact-investor' ),
				'new_item'              => _x( 'New partner', 'Post Type', 'impact-investor' ),
				'add_new'               => _x( 'New partner', 'Post Type', 'impact-investor' ),
				'add_new_item'          => _x( 'Add new partner', 'Post Type', 'impact-investor' ),
				'edit_item'             => _x( 'Edit partner', 'Post Type', 'impact-investor' ),
				'view_item'             => _x( 'View partner', 'Post Type', 'impact-investor' ),
				'view_items'            => _x( 'View partners', 'Post Type', 'impact-investor' ),
				'search_items'          => _x( 'Search partners', 'Post Type', 'impact-investor' ),
				'not_found'             => _x( 'No partners found', 'Post Type', 'impact-investor' ),
				'not_found_in_trash'    => _x( 'No partners found in trash', 'Post Type', 'impact-investor' ),
				'parent_item_colon'     => _x( 'Parent partner:', 'Post Type', 'impact-investor' ),
				'menu_name'             => _x( 'Partners', 'Post Type', 'impact-investor' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => false,
			'rewrite'               => array( 'slug' => __( 'partner', 'impact-investor-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-image-filter',
			'show_in_rest'          => true,
			'rest_base'             => self::POST_TYPE_NAME,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}
}
