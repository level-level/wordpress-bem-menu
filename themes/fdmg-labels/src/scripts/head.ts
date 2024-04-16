import Cookies from 'js-cookie';
import User from './model/user';
import { Auth, ShortInit } from '../globals';

declare const auth: Auth;
declare const short_init: ShortInit;

/**
 * Redirect visitors who have a remember me cookie set to the login page. Those
 * users have been logged out in WordPress but are still remembered by the IDP.
 * Redirecting them to the login page will log them in again if able. If not,
 * they will be redirected to us.
 *
 * @returns true if the visitor was redirected to the IDP, false otherwise
 */
function fdmgLabelsRedirectRememberMeUser(): boolean {
	const rememberMe = Cookies.get( 'fdmg_idp_remember_me' );
	if ( rememberMe === '1' ) {
		Cookies.remove( 'fdmg_idp_remember_me' ); // Remove cookie to prevent endless loop
		const loginUrl = new URL( auth.loginUrl );
		loginUrl.searchParams.set( 'ssoFailureRedirect', window.location.href ); // Redirect on failed rememberme login
		loginUrl.searchParams.set( 'redirect_to', window.location.href ); // Redirect URL on successful rememberme login
		window.location.href = loginUrl.href; // Redirect to IDP login page
		return true;
	}
	return false;
}

/**
 * Determine if the visitor IP should be redirected to the IDP. This is done by
 * sending a request to the IP-to-IDP-redirect endpoint. If the endpoint returns
 * a redirect URL, the visitor will be redirected to the IDP.
 *
 * @returns true if the visitor was redirected to the IDP, false otherwise
 */
async function fdmgLabelsIPToIDPRedirect(): Promise<boolean> {
	try {
		const response = await fetch( `${short_init.url}/ip-to-idp-redirect.php`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
			},
			body: JSON.stringify( {
				redirect_to: window.location.href,
			} ),
		} );
		const responseJson = await response.json();

		if ( responseJson.data.success && responseJson.data.redirect_url ) {
			window.location.href = responseJson.data.redirect_url;
			return true;
		} else {
			return false;
		}
	} catch ( error ) {
		return false;
	}
}

async function fdmgLabelsMaybeRedirectVistorToLogin(): Promise<boolean> {
	if ( User.getCurrentUser() ) {
		return false;
	}

	const redirectedRememberMeUser = fdmgLabelsRedirectRememberMeUser();
	if ( redirectedRememberMeUser ) {
		return true;
	}

	const redirectedIPToIDP = await fdmgLabelsIPToIDPRedirect();
	if ( redirectedIPToIDP ) {
		return true;
	}
	return false;
}
fdmgLabelsMaybeRedirectVistorToLogin();
