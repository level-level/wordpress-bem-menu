<?php
namespace Clarkson\Theme\PostTypes;

class Team {
	public const POST_TYPE_NAME = 'ii_team';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'                  => _x( 'Team', 'Post Type', 'fdmg-labels' ),
				'singular_name'         => _x( 'Team', 'Post Type', 'fdmg-labels' ),
				'all_items'             => _x( 'All team members', 'Post Type', 'fdmg-labels' ),
				'archives'              => _x( 'Team archives', 'Post Type', 'fdmg-labels' ),
				'attributes'            => _x( 'Team attributes', 'Post Type', 'fdmg-labels' ),
				'insert_into_item'      => _x( 'Insert into team member', 'Post Type', 'fdmg-labels' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this team member', 'Post Type', 'fdmg-labels' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'fdmg-labels' ),
				'filter_items_list'     => _x( 'Filter team members list', 'Post Type', 'fdmg-labels' ),
				'items_list_navigation' => _x( 'Team list navigation', 'Post Type', 'fdmg-labels' ),
				'items_list'            => _x( 'Team list', 'Post Type', 'fdmg-labels' ),
				'new_item'              => _x( 'New team member', 'Post Type', 'fdmg-labels' ),
				'add_new'               => _x( 'New team member', 'Post Type', 'fdmg-labels' ),
				'add_new_item'          => _x( 'Add new team member', 'Post Type', 'fdmg-labels' ),
				'edit_item'             => _x( 'Edit team member', 'Post Type', 'fdmg-labels' ),
				'view_item'             => _x( 'View team member', 'Post Type', 'fdmg-labels' ),
				'view_items'            => _x( 'View team members', 'Post Type', 'fdmg-labels' ),
				'search_items'          => _x( 'Search team members', 'Post Type', 'fdmg-labels' ),
				'not_found'             => _x( 'No team members found', 'Post Type', 'fdmg-labels' ),
				'not_found_in_trash'    => _x( 'No team members found in trash', 'Post Type', 'fdmg-labels' ),
				'parent_item_colon'     => _x( 'Parent team member:', 'Post Type', 'fdmg-labels' ),
				'menu_name'             => _x( 'Team', 'Post Type', 'fdmg-labels' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'author', 'fdmg-labels-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-groups',
			'show_in_rest'          => true,
			'rest_base'             => self::POST_TYPE_NAME,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}
}
