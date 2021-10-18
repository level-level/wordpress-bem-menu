<?php
namespace Clarkson\Theme;

use Clarkson\Theme\PostTypes\Example;

class PostTypes {

	protected const POST_TYPES = array(
		Example::class,
	);

	public function register_hooks(): void {
		foreach ( self::POST_TYPES as $post_type_class ) {
			( new $post_type_class() )->register_hooks();
		}
	}

	/**
	 * Register post types
	 *
	 * @psalm-suppress RedundantCondition
	 * Suppressing beacuse if you define a class for 'post' or 'page', you do need the check.
	 */
	public function register_post_types(): void {
		foreach ( self::POST_TYPES as $post_type_class_name ) {
			$post_type_class = new $post_type_class_name();
			if ( is_callable( array( $post_type_class, 'register' ) ) ) {
				$post_type_class->register();
			}
		}
	}
}
