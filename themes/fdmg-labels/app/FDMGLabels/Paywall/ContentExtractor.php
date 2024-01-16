<?php
namespace FDMGLabels\Paywall;

use Clarkson\Theme\ACFPages\Paywall;

class ContentExtractor {

	public function register_hooks(): void {
		add_filter( 'll_paywall_remove_paywall_block', array( $this, 'remove_paywall_block_if_paywall_is_disabled' ) );
	}

	public function remove_paywall_block_if_paywall_is_disabled( bool $remove_paywall_block ): bool {
		$options = ( new Paywall() )->get_options();
		if ( $options[ Paywall::META_PAYWALL_DISABLED ] ?? false ) {
			return true;
		}
		return $remove_paywall_block;
	}
}
