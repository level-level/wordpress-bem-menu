<?php
namespace ImpactInvestor\Sync;

class Init {

	public function register_hooks(): void {
		( new SendNewsletter() )->register_hooks();
	}
}
