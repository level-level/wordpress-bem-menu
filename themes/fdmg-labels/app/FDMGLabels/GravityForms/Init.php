<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\FormSettings\FormSettings;

class Init {

	public function register_hooks(): void {
		( new FormSettings() )->register_hooks();
	}
}
