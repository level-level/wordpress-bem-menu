import Cookies from 'js-cookie';
import User from './model/user';
import { Auth } from '../globals';

declare const auth: Auth;

/**
 * Redirect visitors who have a remember me cookie set but are not logged in
 * to the login page. Those users have been logged out in WordPress but
 * are still remembered by the IDP. Redirecting them to the login page will
 * log them in again if able. If not, they will be redirected to us.
 */
function fdmgLabelsRedirectRememberMeUser() {
	const rememberMe = Cookies.get( 'fdmg_idp_remember_me' );
	if ( rememberMe === '1' && ! User.getCurrentUser() ) {
		Cookies.remove( 'fdmg_idp_remember_me' ); // Remove cookie to prevent endless loop
		const loginUrl = new URL( auth.loginUrl );
		loginUrl.searchParams.set( 'ssoFailureRedirect', window.location.href ); // Redirect on failed rememberme login
		loginUrl.searchParams.set( 'redirect_to', window.location.href ); // Redirect URL on successful rememberme login
		window.location.href = loginUrl.href; // Redirect to IDP login page
	}
}
fdmgLabelsRedirectRememberMeUser();
