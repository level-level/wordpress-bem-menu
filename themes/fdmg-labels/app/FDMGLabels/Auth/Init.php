<?php

namespace FDMGLabels\Auth;

class Init {

	public function register_hooks(): void {
		( new OpenIDConnect() )->register_hooks();
	}

}
