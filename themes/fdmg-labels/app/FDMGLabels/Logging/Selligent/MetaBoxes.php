<?php
namespace FDMGLabels\Logging\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;
use WP_Post;

class MetaBoxes {

	public function register_hooks(): void {
		add_action( 'add_meta_boxes', array( $this, 'add_newsletter_meta_box' ) );
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'add_gravityforms_entry_meta_box' ), 10, 3 );
	}

	public function add_newsletter_meta_box( string $post_type ): void {
		if ( $post_type !== Newsletter::POST_TYPE_NAME ) {
			return;
		}

		add_meta_box(
			'fdmg-labels-selligent-log',
			_x( 'Selligent information', 'Selligent', 'fdmg-labels-admin' ),
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

	public function add_gravityforms_entry_meta_box( array $boxes, array $entry, array $form ): array {
		$boxes['fdmg_labels_selligent_log'] = array(
			'title'    => _x( 'Selligent information', 'Selligent', 'fdmg-labels-admin' ),
			'callback' => array( $this, 'render_gravityforms_entry_meta_box' ),
			'context'  => 'side',
		);
		return $boxes;
	}

	public function render_gravityforms_entry_meta_box( array $args ): void {
		$templates = Templates::get_instance();
		$templates->echo_twig(
			'admin/logging/selligent-metabox.twig', array(
				'object'    => $args['entry'],
				'log_lines' => array_reverse( ( new SelligentLogger() )->get_gravityforms_entry_logs( (int) $args['entry']['id'] ) ),
			)
		);
	}
}
