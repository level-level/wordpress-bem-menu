<?php

namespace FDMGLabels\API;

class APIInit {
	public function register_hooks(): void {
		( new LoginRedirectController() )->register_hooks();
	}
}
