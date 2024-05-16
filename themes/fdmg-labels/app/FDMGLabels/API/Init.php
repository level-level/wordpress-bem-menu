<?php

namespace FDMGLabels\API;

class Init {
	public function register_hooks(): void {
		( new PageFragmentsController() )->register_hooks();
	}

	public function add_script_variables(): void {
		wp_localize_script(
			'clarkson_main',
			'restApi',
			array(
				'root'  => esc_url_raw( rest_url() ),
				'nonce' => wp_create_nonce( 'wp_rest' ),
			)
		);
	}
}
