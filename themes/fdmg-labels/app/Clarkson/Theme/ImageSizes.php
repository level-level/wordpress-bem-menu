<?php
namespace Clarkson\Theme;

class ImageSizes {
	public function register_hooks(): void {
		add_action( 'after_setup_theme', array( $this, 'register_custom_sizes' ) );
		add_filter( 'image_size_names_choose', array( $this, 'add_custom_sizes_to_media_library' ) );
	}

	public function register_custom_sizes(): void {
		add_image_size( 'thumbnail', 170, 170, array( 'center', 'top' ) );
		add_image_size( 'teaser_medium', 512, 288, array( 'center', 'top' ) );
		add_image_size( 'teaser_large', 876, 493, array( 'center', 'top' ) );

		// Used in the content of an article
		add_image_size( 'content_medium', 345 );
		add_image_size( 'content_small', 172 );

		// Used in newsletters
		add_image_size( 'newsletter_large', 720, 480, array( 'center', 'top' ) );
		add_image_size( 'newsletter_thumbnail', 256, 256, array( 'center', 'top' ) );
	}

	/**
	 * Modify the list of image sizes that are available to administrators in the WordPress Media Library
	 * @param  array<string,mixed> $sizes
	 * @return array<string,mixed>
	 */
	public function add_custom_sizes_to_media_library( array $sizes ): array {
		return array_merge(
			$sizes, array(
				'content_medium' => _x( 'Half', 'Image sizes', 'fdmg-labels-admin' ),
				'content_small'  => _x( 'Quarter', 'Image sizes', 'fdmg-labels-admin' ),
			)
		);
	}
}
