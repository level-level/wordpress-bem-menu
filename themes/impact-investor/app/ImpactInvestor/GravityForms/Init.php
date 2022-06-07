<?php

namespace ImpactInvestor\GravityForms;

use ImpactInvestor\GravityForms\FormSettings\FormSettings;

class Init {

	public function register_hooks(): void {
		( new FormSettings() )->register_hooks();
	}
}
