<?php

namespace FDMGLabels\AdminAjax;

class Init {

	public const NONCE = 'fdmg-labels-admin-ajax';

	public function register_hooks(): void {
		( new SendArticleToFDMGContentAPIController() )->register_hooks();
	}

	public function localize_editor(): void {
		wp_localize_script(
			'clarkson_editor',
			'admin_ajax',
			array(
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( self::NONCE ),
			)
		);
	}
}
