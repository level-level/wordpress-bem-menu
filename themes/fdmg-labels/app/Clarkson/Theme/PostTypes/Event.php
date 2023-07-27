<?php
namespace Clarkson\Theme\PostTypes;

use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Event {
	public const POST_TYPE_NAME = 'fdmg_event';

	public const FIELD_GROUP = 'event_group';

	public function register_hooks(): void {
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_EVENT_POST_TYPE ) ) {
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'init', array( $this, 'register_fields' ) );
		}
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'               => _x( 'Events', 'Post Type', 'fdmg-labels-admin' ),
				'singular_name'      => _x( 'Event', 'Post Type', 'fdmg-labels-admin' ),
				'all_items'          => _x( 'All events', 'Post Type', 'fdmg-labels-admin' ),
				'archives'           => _x( 'Events archives', 'Post Type', 'fdmg-labels-admin' ),
				'add_new'            => _x( 'New event', 'Post Type', 'fdmg-labels-admin' ),
				'edit_item'          => _x( 'Edit event', 'Post Type', 'fdmg-labels-admin' ),
				'view_item'          => _x( 'View event', 'Post Type', 'fdmg-labels-admin' ),
				'view_items'         => _x( 'View events', 'Post Type', 'fdmg-labels-admin' ),
				'search_items'       => _x( 'Search events', 'Post Type', 'fdmg-labels-admin' ),
				'not_found'          => _x( 'No events found', 'Post Type', 'fdmg-labels-admin' ),
				'not_found_in_trash' => _x( 'No events found in trash', 'Post Type', 'fdmg-labels-admin' ),
				'parent_item_colon'  => _x( 'Parent event:', 'Post Type', 'fdmg-labels-admin' ),
			),
			'public'                => true,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => true,
			'supports'              => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
			'has_archive'           => true,
			'rewrite'               => array( 'slug' => __( 'event', 'fdmg-labels-admin-slugs' ) ),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-calendar',
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
				'title'    => _x( 'Event', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(),
				'location' => array(
					Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
				),
				'position' => 'side',
			)
		);
	}
}
