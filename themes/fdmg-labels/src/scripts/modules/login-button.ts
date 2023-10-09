import { IModule } from '../../globals';
import Auth from '../helpers/auth';

class LoginButton implements IModule {
	element: HTMLElement;
	auth: Auth;
	loginButton: HTMLLinkElement | null;
	logoutButton: HTMLLinkElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.auth = new Auth();
		this.loginButton = this.element.querySelector('.js-login');
		this.logoutButton = this.element.querySelector('.js-logout');

		this.initialize();
	}

	initialize() {
		if (this.auth.userIsAuthenticated()) {
			this.showLogOutButton();
		} else {
			this.showLoginButton();
		}
	}

	showLoginButton() {
		this.logoutButton?.remove();
	}

	showLogOutButton() {
		this.loginButton?.remove();
	}
}

export default LoginButton;
