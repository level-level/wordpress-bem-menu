<?php

namespace Clarkson\Theme\Taxonomies;

use Clarkson\Theme\PostTypes\Post;

class Category {

	public const TAXONOMY_NAME = 'category';
	protected const POST_TYPES = array( Post::POST_TYPE_NAME );

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
	}

	public function register(): void {
		foreach ( self::POST_TYPES as $post_type ) {
			register_taxonomy_for_object_type( self::TAXONOMY_NAME, $post_type );
		}
	}
}
