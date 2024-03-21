<?php

namespace FDMGLabels\Paywall;

use Clarkson\Theme\ACFPages\Paywall;
use Clarkson\WPadmin\Capabilities;
use LevelLevel\Paywall\ObjectPaywall;

class PaywallObject extends ObjectPaywall {

	public function get_require_roles_for_access(): bool {
		return (bool) ( $this->field_values[ ACFFields::META_REQUIRE_ROLES_FOR_ACCESS ] ?? true );
	}

	/**
	 * @return array<string>
	 */
	public function get_allowed_user_roles(): array {
		$roles   = $this->field_values[ ACFFields::META_ALLOWED_USER_ROLES ] ?? null;
		$default = array( Capabilities::ROLE_FDMG_SUBSCRIBER );
		return is_array( $roles ) ? $roles : $default;
	}

	/**
	 * @return array<string,mixed>
	 */
	public function get_paywall_options(): array {
		return ( new Paywall() )->get_options();
	}
}
