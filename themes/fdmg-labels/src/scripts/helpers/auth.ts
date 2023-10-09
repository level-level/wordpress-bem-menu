import Cookies from 'js-cookie';

class Auth {
	userCookie: string;
	cookieValue: string | undefined;

	constructor() {
		this.userCookie = 'fdmg_labels_user';
		this.cookieValue = Cookies.get(this.userCookie);
	}

	userIsAuthenticated() {
		return !!this.cookieValue; // Returns true if cookieValue is truthy, false otherwise
	}
}

export default Auth;
