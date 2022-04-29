<?php
namespace ImpactInvestor\Logging\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use WP_Post;

class MetaBoxes {

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_newsletter_meta_box' ) );
	}

	public function add_newsletter_meta_box( string $post_type ): void {
		if ( $post_type !== Newsletter::POST_TYPE_NAME ) {
			return;
		}

		add_meta_box(
			'impact-investor-selligent-log',
			__( 'Selligent information', 'impact-investor' ),
			array( $this, 'render_newsletter_meta_box' ),
			null,
			'side',
			'low'
		);
	}

	public function render_newsletter_meta_box( WP_Post $post ): void {
		$templates = Templates::get_instance();
		$templates->echo_twig(
			'admin/logging/selligent-metabox.twig', array(
				'object'    => Objects::get_instance()->get_object( $post ),
				'log_lines' => array_reverse( ( new SelligentLogger() )->get_post_logs( $post->ID ) ),
			)
		);
	}
}
