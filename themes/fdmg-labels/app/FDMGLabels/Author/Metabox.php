<?php
namespace FDMGLabels\Author;

use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\post;
use WP_Post;

class Metabox {

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_metabox' ), 10, 2 );
	}

	public function add_metabox( string $post_type, WP_Post $post ): void {
		$clarkson_object = Objects::get_instance()->get_object( $post );
		if ( ! $clarkson_object instanceof post ) {
			return;
		}

		add_meta_box(
			'fdmg-quick-add-author',
			__( 'Quickly add author', 'fdmg-labels' ),
			array( $this, 'render_metabox' ),
			PostTypesPost::POST_TYPE_NAME,
			'side',
			'low'
		);
	}

	public function render_metabox( WP_Post $post ): void {
		$clarkson_object = Objects::get_instance()->get_object( $post );
		if ( ! $clarkson_object instanceof post ) {
			return;
		}

		Templates::get_instance()->echo_twig(
			'admin/metabox/add-author-form.twig',
			array(),
			true
		);
	}
}
