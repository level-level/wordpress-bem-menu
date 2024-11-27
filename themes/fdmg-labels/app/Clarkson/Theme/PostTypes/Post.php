<?php
namespace Clarkson\Theme\PostTypes;

use Clarkson\Theme\ACFBlocks\RelatedArticles;
use Clarkson\Theme\ACFBlocks\SocialShare;
use Clarkson\Theme\ACFPages\Paywall;
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
		add_action( 'init', array( $this, 'change_post_object_strings' ) );
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Article', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Relationship::make( _x( 'Article author', 'Fields', 'fdmg-labels-admin' ), self::META_AUTHORS )
						->instructions( _x( 'Select the author of this article.', 'Fields', 'fdmg-labels-admin' ) )
						->postTypes( array( Team::POST_TYPE_NAME ) )
						->filters( array( 'search' ) )
						->returnFormat( 'object' )
						->elements( array( 'featured_image' ) ),
					Text::make( _x( 'Article title', 'Fields', 'fdmg-labels-admin' ), self::META_ALTERNATIVE_TITLE )
						->instructions( _x( 'Use this field to overwrite the article title in the teaser.', 'Fields', 'fdmg-labels-admin' ) ),
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

	/**
	 * Add default blocks to the post type.
	 * Add the paywall block to the template when the default active option is enabled in the Paywall options page.
	 */
	public function block_template(): void {
		$post_type_object = get_post_type_object( self::POST_TYPE_NAME );

		if ( ! $post_type_object instanceof WP_Post_Type ) {
			return;
		}

		$paywall_options = get_field( Paywall::META_PAYWALL, 'options' ) ?: array();

		$soft_template = array(
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Introduction text..', 'Block', 'fdmg-labels-admin' ),
					'fontSize'    => 'intro',
				),
			),
			array(
				'core/paragraph',
				array(
					'placeholder' => _x( 'Start article text..', 'Block', 'fdmg-labels-admin' ),
				),
			),
			array( 'acf/block-' . SocialShare::BLOCK_NAME ),
			array( 'acf/block-' . RelatedArticles::BLOCK_NAME ),
		);

		// Adds paywall block after the first block.
		if ( ! empty( $paywall_options ) && $paywall_options[ Paywall::META_PAYWALL_DEFAULT_ACTIVE ] ) {
			array_splice(
				$soft_template, 1, 0, array(
					array( 'acf/block-ll-paywall-paywall' ),
				)
			);
		}

		$post_type_object->template = $soft_template;
	}

	/**
	 * Change the post object labels so we can translate them to a more custom label for the Dutch fdmg label sites.
	 */
	public function change_post_object_strings(): void {
		$get_post_type = get_post_type_object( self::POST_TYPE_NAME );

		if ( ! $get_post_type instanceof WP_Post_Type ) {
			return;
		}

		$labels                        = $get_post_type->labels;
		$labels->featured_image        = _x( 'Featured image', 'Custom Post', 'fdmg-labels-admin' );
		$labels->set_featured_image    = _x( 'Set featured image', 'Custom Post', 'fdmg-labels-admin' );
		$labels->remove_featured_image = _x( 'Remove featured image', 'Custom Post', 'fdmg-labels-admin' );
		$labels->use_featured_image    = _x( 'Use featured image', 'Custom Post', 'fdmg-labels-admin' );
	}
}
