<?php
namespace Clarkson\Theme\PostTypes;

use Clarkson\Theme\PostTypes\Team;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Location;
use WP_Post_Type;

class Post {
	public const POST_TYPE_NAME = 'post';

	public const FIELD_GROUP = 'post_authors';

	public const META_AUTHORS = 'authors';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_action( 'init', array( $this, 'block_template' ) );
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Authors', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Relationship::make( _x( 'Authors', 'Fields', 'fdmg-labels' ), self::META_AUTHORS )
						->instructions( _x( 'Select the author of this article.', 'Fields', 'fdmg-labels' ) )
						->postTypes( array( Team::POST_TYPE_NAME ) )
						->filters( array( 'search' ) )
						->returnFormat( 'object' )
						->elements( array( 'featured_image' ) ),
				),
				'location' => array(
					Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
				),
				'position' => 'side',
			)
		);
	}

	public function block_template(): void {
		$post_type_object = get_post_type_object( self::POST_TYPE_NAME );

		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return;
		}

		$post_type_object->template = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Introduction text..', 'Block', 'fdmg-labels' ),
					'fontSize'    => 'intro',
				),
			),
			array( 'core/heading' ),
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Start article text..', 'Block', 'fdmg-labels' ),
				),
			),
			array( 'acf/block-highlighted-content' ),
			array( 'acf/block-social-share' ),
			array( 'acf/block-related-articles' ),
		);
	}

}
