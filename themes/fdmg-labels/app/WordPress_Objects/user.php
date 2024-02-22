<?php

namespace Clarkson_Core\WordPress_Object;

use FDMGLabels\ServerSideTracking\Init as ServerSideTrackingInit;

class user extends Clarkson_User {

	public function get_fdmg_user_id(): int {
		return (int) $this->get_meta( ServerSideTrackingInit::META_FDMG_USER_ID, true );
	}

	public function get_fdmg_organisation_id(): int {
		return (int) $this->get_meta( ServerSideTrackingInit::META_FDMG_ORGANISATION_ID, true );
	}
}
