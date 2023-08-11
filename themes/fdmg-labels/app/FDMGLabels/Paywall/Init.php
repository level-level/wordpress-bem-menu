<?php
namespace FDMGLabels\Paywall;

class Init {

	public function register_hooks(): void {
		( new ContentAccess() )->register_hooks();
	}

}
