<?php
namespace Clarkson\Theme\PostTypes;

use Clarkson\Theme\PostTypes\Team;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Location;
class Post {
	public const POST_TYPE_NAME = 'post';

	public const FIELD_GROUP = 'post_authors';

	public const META_AUTHORS = 'authors';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( '[Post] Authors', 'Fields', 'fdmg-labels' ),
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

}
