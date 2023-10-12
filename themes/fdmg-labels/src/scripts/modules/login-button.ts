import { I18n, IModule } from '../../globals';
import User from '../model/user';

declare const i18n: I18n;

class LoginButton implements IModule {
	element: HTMLElement;
	user: User | null;
	loginButton: HTMLLinkElement | null;
	logoutButton: HTMLLinkElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.user = User.getCurrentUser();
		this.loginButton = this.element.querySelector('.js-login');
		this.logoutButton = this.element.querySelector('.js-logout');

		this.initialize();
	}

	initialize() {
		if (this.user) {
			this.showLogOutButton();
			this.showAndCreateMessage();
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

	showAndCreateMessage() {
		const element = document.createElement('p');
		element.className = 'text-base';
		element.textContent = `${i18n.loggedIn} ${this.user?.getDisplayName()}`;
		this.element.insertBefore(element, this.element.firstChild);
	}
}

export default LoginButton;
