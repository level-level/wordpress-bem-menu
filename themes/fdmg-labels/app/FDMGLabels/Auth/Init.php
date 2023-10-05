<?php

namespace FDMGLabels\Auth;

class Init {

	public function register_hooks(): void {
		( new UserCookies() )->register_hooks();
		( new FDMGIdentification() )->register_hooks();
		( new OpenIDConnect() )->register_hooks();
	}

}
