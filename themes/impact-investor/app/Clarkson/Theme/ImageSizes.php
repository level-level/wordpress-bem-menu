<?php
namespace Clarkson\Theme;

class ImageSizes {
	public function register_hooks(): void {
		add_action( 'after_setup_theme', array( $this, 'register_custom_sizes' ) );
	}

	public function register_custom_sizes(): void {
		add_image_size( 'thumbnail', 115, 115, array( 'center', 'top' ) );
		add_image_size( 'teaser_medium', 512, 288, array( 'center', 'top' ) );
	}
}
