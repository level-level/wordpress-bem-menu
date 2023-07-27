<?php
namespace Clarkson\Theme\PostTypes;

use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class Partner {
	public const POST_TYPE_NAME = 'ii_partner';

	public const FIELD_GROUP = 'partner_data';

	public const META_SUBTITLE = 'subtitle';

	public function register_hooks(): void {
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_PARTNER_POST_TYPE ) ) {
			add_action( 'init', array( $this, 'register' ) );
			add_action( 'init', array( $this, 'register_fields' ) );
		}
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'               => _x( 'Partner', 'Post Type', 'fdmg-labels-admin' ),
				'singular_name'      => _x( 'Partner', 'Post Type', 'fdmg-labels-admin' ),
				'all_items'          => _x( 'All partners', 'Post Type', 'fdmg-labels-admin' ),
				'archives'           => _x( 'Partner archives', 'Post Type', 'fdmg-labels-admin' ),
				'add_new'            => _x( 'New partner', 'Post Type', 'fdmg-labels-admin' ),
				'edit_item'          => _x( 'Edit partner', 'Post Type', 'fdmg-labels-admin' ),
				'view_item'          => _x( 'View partner', 'Post Type', 'fdmg-labels-admin' ),
				'view_items'         => _x( 'View partners', 'Post Type', 'fdmg-labels-admin' ),
				'search_items'       => _x( 'Search partners', 'Post Type', 'fdmg-labels-admin' ),
				'not_found'          => _x( 'No partners found', 'Post Type', 'fdmg-labels-admin' ),
				'not_found_in_trash' => _x( 'No partners found in trash', 'Post Type', 'fdmg-labels-admin' ),
				'parent_item_colon'  => _x( 'Parent partner:', 'Post Type', 'fdmg-labels-admin' ),
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

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Partner', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Subtitle', 'Fields', 'fdmg-labels-admin' ), self::META_SUBTITLE )
						->instructions( 'The subtitle that is displayed in the right sidebar underneath the logo.' ),
				),
				'location' => array(
					Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
				),
				'position' => 'side',
			)
		);
	}
}
