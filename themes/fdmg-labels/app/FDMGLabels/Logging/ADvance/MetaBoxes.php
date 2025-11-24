<?php
namespace FDMGLabels\Logging\ADvance;

use Clarkson\Utilities\I18n;
use Clarkson_Core\Templates;
use FDMGLabels\Logging\ADvance\ADvanceLogger;

class MetaBoxes {

	public function register_hooks(): void {
		add_filter( 'gform_entry_detail_meta_boxes', array( $this, 'add_gravityforms_entry_meta_box' ), 10, 3 );
	}

	/**
	 * @param array<string,array<string,mixed>> $boxes
	 * @param array<string|int,mixed> $entry
	 * @param array<string,mixed> $form
	 * @return array<string,array<string,mixed>>
	 */
	public function add_gravityforms_entry_meta_box( array $boxes, array $entry, array $form ): array {
		$boxes['fdmg_advance_log'] = array(
			'title'    => _x( 'ADvance information', 'ADvance', 'fdmg-advance-admin' ),
			'callback' => array( $this, 'render_gravityforms_entry_meta_box' ),
			'context'  => 'side',
		);
		return $boxes;
	}

	/**
	 * @param array<string,mixed> $args
	 */
	public function render_gravityforms_entry_meta_box( array $args ): void {
		$templates = Templates::get_instance();
		$templates->echo_twig(
			'admin/logging/advance-metabox.twig', array(
				'log_lines' => array_reverse( ( new ADvanceLogger() )->get_gravityforms_entry_logs( (int) $args['entry']['id'] ) ),
			),
			true
		);
	}
}
