<?php
namespace Clarkson\Theme\PostTypes;

use Extended\ACF\Fields\Email;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Url;
use Extended\ACF\Location;

class Team {
	public const POST_TYPE_NAME = 'ii_team';

	public const FIELD_GROUP = 'author_data';

	public const META_EMAIL    = 'email';
	public const META_LINKEDIN = 'linkedin';
	public const META_LOCATION = 'location';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'               => _x( 'Team/Author', 'Post Type', 'fdmg-labels-admin' ),
				'singular_name'      => _x( 'Team', 'Post Type', 'fdmg-labels-admin' ),
				'all_items'          => _x( 'All team members', 'Post Type', 'fdmg-labels-admin' ),
				'archives'           => _x( 'Team archives', 'Post Type', 'fdmg-labels-admin' ),
				'add_new'            => _x( 'New team member', 'Post Type', 'fdmg-labels-admin' ),
				'edit_item'          => _x( 'Edit team member', 'Post Type', 'fdmg-labels-admin' ),
				'view_item'          => _x( 'View team member', 'Post Type', 'fdmg-labels-admin' ),
				'view_items'         => _x( 'View team members', 'Post Type', 'fdmg-labels-admin' ),
				'search_items'       => _x( 'Search team members', 'Post Type', 'fdmg-labels-admin' ),
				'not_found'          => _x( 'No team members found', 'Post Type', 'fdmg-labels-admin' ),
				'not_found_in_trash' => _x( 'No team members found in trash', 'Post Type', 'fdmg-labels-admin' ),
				'parent_item_colon'  => _x( 'Parent team member:', 'Post Type', 'fdmg-labels-admin' ),
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

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Author', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Email::make( _x( 'E-mail address', 'Fields', 'fdmg-labels-admin' ), self::META_EMAIL ),
					Url::make( _x( 'LinkedIn', 'Fields', 'fdmg-labels-admin' ), self::META_LINKEDIN )
					->placeholder( 'https://linkedin.com/' ),
					Text::make( _x( 'Location', 'Fields', 'fdmg-labels-admin' ), self::META_LOCATION ),
				),
				'location' => array(
					Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
				),
				'position' => 'side',
			)
		);
	}
}
