<?php
namespace Clarkson\Theme;

use Clarkson\Theme\PostTypes\Post;
use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;

class Taxonomies {

	public const TAXONOMIES = array(
		Category::class,
		Genre::class,
		Section::class,
	);

	public function register_hooks(): void {
		foreach ( self::TAXONOMIES as $taxonomy_class ) {
			( new $taxonomy_class() )->register_hooks();
		}

		add_action( 'init', array( $this, 'unregister_post_tags' ) );
	}

	public function unregister_post_tags(): void {
		unregister_taxonomy_for_object_type( 'post_tag', Post::POST_TYPE_NAME );
	}
}
