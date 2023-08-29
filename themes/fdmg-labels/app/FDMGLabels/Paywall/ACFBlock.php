<?php

namespace FDMGLabels\Paywall;

use Clarkson\Theme\ACFBlocks;

class ACFBlock {

	public function register_hooks(): void {
		add_filter( 'll_paywall_paywall_block_settings', array( $this, 'change_settings' ) );
	}

	/**
	 * Overwrite settings for the block
	 *
	 * @param array<string,mixed> $block
	 * @return array<string,mixed>
	 */
	public function change_settings( array $block ): array {
		$block['render_callback'] = array( new ACFBlocks(), 'block_render_callback' );
		$block['align']           = 'center';
		$block['supports']        = array(
			'align'    => array( 'center' ),
			'multiple' => false,
		);
		return $block;
	}
}
