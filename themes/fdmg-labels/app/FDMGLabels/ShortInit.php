<?php

namespace FDMGLabels;

class ShortInit {
	public function localize_head(): void {
		wp_localize_script(
			'clarkson_head',
			'short_init',
			array(
				'url'   => home_url( 'wp-content/endpoints' ),
			)
		);
	}
}
