<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\Templates;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use WP_Post;

class MergeTagsMetabox {

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
	}

	public function add_meta_box( string $post_type ): void {
		if ( $post_type !== Newsletter::POST_TYPE_NAME ) {
			return;
		}

		add_meta_box(
			'fdmg-labels-newsletter-merge-tags',
			_x( 'Merge tags', 'Newsletter', 'fdmg-labels-admin' ),
			array( $this, 'render_meta_box' ),
			null,
			'side',
			'low'
		);
	}

	public function render_meta_box( WP_Post $post ): void {
		$templates = Templates::get_instance();
		$templates->echo_twig(
			'admin/newsletter/merge-tags-metabox.twig',
			array(),
			true
		);
	}
}
