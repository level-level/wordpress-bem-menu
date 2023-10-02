<?php
namespace FDMGLabels\Logging\FDMGContentAPI;

use Clarkson\Theme\PostTypes\Post;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use FDMGLabels\AdminAjax\Init as AdminAjaxInit;
use FDMGLabels\AdminAjax\SendArticleToFDMGContentAPIController;
use FDMGLabels\Connectors\FDMGContentAPI\Configuration;
use WP_Post;

class MetaBoxes {

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_post_meta_box' ) );
	}

	public function add_post_meta_box( string $post_type ): void {
		if ( $post_type !== Post::POST_TYPE_NAME ) {
			return;
		}

		if ( ! ( new Configuration() )->is_enabled() ) {
			return;
		}

		add_meta_box(
			'fdmg-labels-fdmg-content-api-log',
			_x( 'FDMG Content API information', 'FDMG Content API', 'fdmg-labels-admin' ),
			array( $this, 'render_post_meta_box' ),
			null,
			'side',
			'low'
		);
	}

	public function render_post_meta_box( WP_Post $post ): void {
		$templates = Templates::get_instance();
		$templates->echo_twig(
			'admin/logging/fdmg-content-api-metabox.twig', array(
				'object'                       => Objects::get_instance()->get_object( $post ),
				'log_lines'                    => array_reverse( ( new FDMGContentAPILogger() )->get_post_logs( $post->ID ) ),
			),
			true
		);
	}
}
