<?php
namespace Clarkson\Theme;

class Support {
	public function register_hooks(): void {
		add_filter( 'clarkson_twig_functions', array( $this, 'add_custom_functions' ) );
		add_action( 'after_setup_theme', array( $this, 'add_theme_supports' ) );
	}

	public function add_theme_supports(): void {
		// Enable plugins to manage the document title
		// http://codex.wordpress.org/Function_Reference/add_theme_support#Title_Tag
		add_theme_support( 'title-tag' );

		// Enable post thumbnails
		// http://codex.wordpress.org/Post_Thumbnails
		// http://codex.wordpress.org/Function_Reference/set_post_thumbnail_size
		// http://codex.wordpress.org/Function_Reference/add_image_size
		add_theme_support( 'post-thumbnails' );

		// Enable HTML5 markup support
		// http://codex.wordpress.org/Function_Reference/add_theme_support#HTML5
		add_theme_support( 'html5', array( 'caption', 'comment-form', 'comment-list', 'gallery', 'search-form' ) );

		// Make theme available for translation
		load_theme_textdomain( 'impact-investor', get_template_directory() . '/languages' );
	}

	public function add_custom_functions( array $functions ): array {
		$theme_functions = array(
			'get_home_url',
			'the_search_query',
			'sanitize_title',
			'bem_menu',
			'function_exists',
			'yoast_breadcrumb',
			'get_search_query',
			'wp_strip_all_tags',
			'uniqid',
			'get_post_thumbnail_id',
			'wp_get_nav_menu_name',
			'edit_comment_link',
		);
		$functions       = array_merge( $functions, $theme_functions );
		return $functions;
	}
}
