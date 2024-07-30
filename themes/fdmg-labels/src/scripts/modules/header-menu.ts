import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';
import User from '../model/user';


class HeaderMenu implements IModule {
	element: HTMLElement;
	user: User | null;
	backdrop: HTMLElement | null;
	html: HTMLElement | null;
	dropdown: HTMLElement | null;
	closeButton: HTMLButtonElement | null;
	profileNav: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.user = User.getCurrentUser();
		this.backdrop = this.element.querySelector('.js-header-menu-backdrop');
		this.html = document.querySelector('html');
		this.dropdown = this.element.querySelector('.js-header-menu-dropdown');
		this.closeButton = this.element.querySelector('.js-close-menu');
		this.profileNav = this.element.querySelector('.js-profile-nav');

		this.initialize();
	}

	initialize() {
		const options = {
			moduleName: 'headerMenu',
			useHover: false,
		};

		const toggle = new MrToggle(this.element, <IOptions>options);

		this.backdrop?.addEventListener('click', () => {
			toggle.closeAllToggles();
		});

		this.closeButton?.addEventListener('click', () => {
			toggle.close();
		});

		this.element.addEventListener('openMrToggle', () => {
			this.showBackdrop();

			// Close button is only available in the compact version of the header
			if (this.closeButton) {
				this.element.querySelector('.btn-menu')?.setAttribute('tabindex', '-1');

				setTimeout(() => this.closeButton?.focus(), 300);
			}

			// Set the html element to height 100vh to fix a bug on iOS Safari 15
			// https://stackoverflow.com/questions/69355787/mobile-safari-15-bottom-navigation-area-issue
			if (this.html) {
				this.html.style.height = '100vh';
			}
		});

		this.element.addEventListener('closeMrToggle', () => {
			if (this.dropdown?.getAttribute('aria-hidden') === 'true') {
				this.hideBackdrop();
			}

			// Close button is only available in the compact version of the header
			if (this.closeButton) {
				this.element.querySelector('.btn-menu')?.removeAttribute('tabindex');
				this.element.querySelector<HTMLButtonElement>('.btn-menu')?.focus();
			}

			// Remove style property that fixes iOS Safari 15 issue
			this.html?.removeAttribute('style');
		});

		if (!this.user) {
			// If the user is not logged in, remove the profile nav
			this.profileNav?.remove();
		}
	}

	showBackdrop() {
		this.backdrop?.setAttribute('aria-hidden', 'false');
	}

	hideBackdrop() {
		this.backdrop?.setAttribute('aria-hidden', 'true');
	}
}

export default HeaderMenu;
