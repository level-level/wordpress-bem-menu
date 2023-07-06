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
				'name'                  => _x( 'Partner', 'Post Type', 'fdmg-labels' ),
				'singular_name'         => _x( 'Partner', 'Post Type', 'fdmg-labels' ),
				'all_items'             => _x( 'All partners', 'Post Type', 'fdmg-labels' ),
				'archives'              => _x( 'Partner archives', 'Post Type', 'fdmg-labels' ),
				'attributes'            => _x( 'Partner attributes', 'Post Type', 'fdmg-labels' ),
				'insert_into_item'      => _x( 'Insert into partner', 'Post Type', 'fdmg-labels' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this partner', 'Post Type', 'fdmg-labels' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'fdmg-labels' ),
				'filter_items_list'     => _x( 'Filter partners list', 'Post Type', 'fdmg-labels' ),
				'items_list_navigation' => _x( 'Partner list navigation', 'Post Type', 'fdmg-labels' ),
				'items_list'            => _x( 'Partner list', 'Post Type', 'fdmg-labels' ),
				'new_item'              => _x( 'New partner', 'Post Type', 'fdmg-labels' ),
				'add_new'               => _x( 'New partner', 'Post Type', 'fdmg-labels' ),
				'add_new_item'          => _x( 'Add new partner', 'Post Type', 'fdmg-labels' ),
				'edit_item'             => _x( 'Edit partner', 'Post Type', 'fdmg-labels' ),
				'view_item'             => _x( 'View partner', 'Post Type', 'fdmg-labels' ),
				'view_items'            => _x( 'View partners', 'Post Type', 'fdmg-labels' ),
				'search_items'          => _x( 'Search partners', 'Post Type', 'fdmg-labels' ),
				'not_found'             => _x( 'No partners found', 'Post Type', 'fdmg-labels' ),
				'not_found_in_trash'    => _x( 'No partners found in trash', 'Post Type', 'fdmg-labels' ),
				'parent_item_colon'     => _x( 'Parent partner:', 'Post Type', 'fdmg-labels' ),
				'menu_name'             => _x( 'Partners', 'Post Type', 'fdmg-labels' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => false,
			'rewrite'               => array( 'slug' => __( 'partner', 'fdmg-labels-slugs' ) ),
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
