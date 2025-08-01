<?php
namespace FDMGLabels\Auth\IdentityProviders\ADvance;

use FDMGLabels\Auth\IdentityProviders\IdentityProviderInterface;

class ADvanceIdentityProvider implements IdentityProviderInterface {

	public function get_id(): string {
		return 'advance';
	}

	public function register_hooks(): void {}

	public function is_available(): bool {
		return false;
	}

}
