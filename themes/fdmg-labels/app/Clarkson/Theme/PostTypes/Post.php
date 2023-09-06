<?php
namespace Clarkson\Theme\PostTypes;

use Clarkson\Theme\ACFBlocks\HighlightedContent;
use Clarkson\Theme\ACFBlocks\RelatedArticles;
use Clarkson\Theme\ACFBlocks\SocialShare;
use Clarkson\Theme\PostTypes\Team;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Location;
use WP_Post_Type;

class Post {
	public const POST_TYPE_NAME = 'post';

	public const FIELD_GROUP = 'post_authors';

	public const META_AUTHORS           = 'authors';
	public const META_ALTERNATIVE_TITLE = 'alternative_title';
	public const META_WORK_INSTRUCTIONS = 'work_instructions';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_action( 'init', array( $this, 'block_template' ) );
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Post', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Article title', 'Fields', 'fdmg-labels-admin' ), self::META_ALTERNATIVE_TITLE )
						->instructions( _x( 'Use this field to overwrite the article title in the teaser.', 'Fields', 'fdmg-labels-admin' ) ),
					Relationship::make( _x( 'Author', 'Fields', 'fdmg-labels-admin' ), self::META_AUTHORS )
						->instructions( _x( 'Select the author of this article.', 'Fields', 'fdmg-labels-admin' ) )
						->postTypes( array( Team::POST_TYPE_NAME ) )
						->filters( array( 'search' ) )
						->returnFormat( 'object' )
						->elements( array( 'featured_image' ) ),
					Textarea::make( _x( 'Work instructions', 'Fields', 'fdmg-labels-admin' ), self::META_WORK_INSTRUCTIONS )
						->instructions( _x( 'Use this field to add instructions or notes inside the backend of an article. This field is not shown publicly on the front-end.', 'Fields', 'fdmg-labels-admin' ) ),
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
					'placeholder' => _x( 'Introduction text..', 'Block', 'fdmg-labels-admin' ),
					'fontSize'    => 'intro',
				),
			),
			array( 'core/heading' ),
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Start article text..', 'Block', 'fdmg-labels-admin' ),
				),
			),
			array( 'acf/block-' . HighlightedContent::BLOCK_NAME ),
			array( 'acf/block-' . SocialShare::BLOCK_NAME ),
			array( 'acf/block-' . RelatedArticles::BLOCK_NAME ),
		);
	}

}
