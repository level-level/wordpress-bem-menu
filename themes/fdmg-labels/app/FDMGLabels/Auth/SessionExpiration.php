<?php

namespace FDMGLabels\Auth;

use WP_User;

class SessionExpiration {

	public const SESSION_EXPIRATION_MINUTES = 30;

	public function register_hooks(): void {
		add_filter( 'auth_cookie_expiration', array( $this, 'limit_session_expiration_for_non_editors' ), 10, 2 );
	}

	/**
	 * If a user has no access to the WP-admin portal, we assume this is a
	 * normal visitor with a user account, logging in via the IDP. In that case
	 * we want to force set their WordPress session length to be 30 minutes.
	 * Because of the fdmg_idp_remember_me cookie, those users will after 30
	 * minutes be redirected to the IDP to re-authenticate. This will
	 * re-authenticate the user again for 30 minutes (without logging in again
	 * if able). This will also trigger logging in the IDP, which FDMG uses to
	 * track user activity and invoice business customers accordingly based on
	 * the amount of active logged in users on the website.
	 */
	public function limit_session_expiration_for_non_editors( int $expiration, int $user_id ): int {
		if ( user_can( $user_id, 'read' ) ) {
			return $expiration;
		}

		return self::SESSION_EXPIRATION_MINUTES * MINUTE_IN_SECONDS;
	}
}
