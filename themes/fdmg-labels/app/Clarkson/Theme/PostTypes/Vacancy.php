<?php
namespace Clarkson\Theme\PostTypes;

use Extended\ACF\Fields\Text;
use Extended\ACF\Location;

class Vacancy {
	public const POST_TYPE_NAME = 'ii_vacancy';
	public const FIELD_GROUP    = 'ii_vacancy_group';

	public const META_COMPANY  = 'company';
	public const META_LOCATION = 'location';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'               => _x( 'Vacancies', 'Post Type', 'fdmg-labels-admin' ),
				'singular_name'      => _x( 'Vacancy', 'Post Type', 'fdmg-labels-admin' ),
				'all_items'          => _x( 'All vacancies', 'Post Type', 'fdmg-labels-admin' ),
				'archives'           => _x( 'Vacancy archives', 'Post Type', 'fdmg-labels-admin' ),
				'add_new'            => _x( 'New vacancy', 'Post Type', 'fdmg-labels-admin' ),
				'edit_item'          => _x( 'Edit vacancy', 'Post Type', 'fdmg-labels-admin' ),
				'view_item'          => _x( 'View vacancy', 'Post Type', 'fdmg-labels-admin' ),
				'view_items'         => _x( 'View vacancies', 'Post Type', 'fdmg-labels-admin' ),
				'search_items'       => _x( 'Search vacancies', 'Post Type', 'fdmg-labels-admin' ),
				'not_found'          => _x( 'No vacancies found', 'Post Type', 'fdmg-labels-admin' ),
				'not_found_in_trash' => _x( 'No vacancies found in trash', 'Post Type', 'fdmg-labels-admin' ),
				'parent_item_colon'  => _x( 'Parent vacancy:', 'Post Type', 'fdmg-labels-admin' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'vacancies', 'fdmg-labels-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => 25,
			'menu_icon'             => 'dashicons-money',
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
				'title'    => _x( 'Article', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Company name', 'Fields', 'fdmg-labels-admin' ), self::META_COMPANY ),
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
