<?php

namespace FDMGLabels\Paywall;

use LevelLevel\Paywall\ObjectPaywall;

class PaywallObject extends ObjectPaywall {

	public function get_require_roles_for_access(): bool {
		return (bool) ( $this->field_values[ ACFFields::META_REQUIRE_ROLES_FOR_ACCESS ] ?? false );
	}

	/**
	 * @return array<string>
	 */
	public function get_allowed_user_roles(): array {
		$roles = $this->field_values[ ACFFields::META_ALLOWED_USER_ROLES ] ?? array();
		return is_array( $roles ) ? $roles : array();
	}

}
