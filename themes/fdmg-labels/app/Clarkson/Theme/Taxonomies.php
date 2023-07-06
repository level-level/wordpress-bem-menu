<?php
namespace Clarkson\Theme;

class Taxonomies {

	protected const TAXONOMIES = array();

	public function register_hooks(): void {
		foreach ( self::TAXONOMIES as $taxonomy_class ) {
			( new $taxonomy_class() )->register_hooks();
		}

		add_action( 'init', array( $this, 'unregister_post_tags' ) );
	}

	public function unregister_post_tags(): void {
		unregister_taxonomy_for_object_type( 'post_tag', 'post' );
	}
}
