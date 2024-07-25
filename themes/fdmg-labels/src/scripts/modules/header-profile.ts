import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';
import User from '../model/user';

class HeaderProfile implements IModule {
	element: HTMLElement;
	user: User | null;
	backdrop: HTMLElement | null;
	profileNav: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.user = User.getCurrentUser();
		this.backdrop = this.element.querySelector('.js-header-profile-backdrop');
		this.profileNav = this.element.querySelector('.js-profile-nav');
		this.initialize();
	}

	initialize() {
		const options = {
			useHover: false,
		};

		const toggle = new MrToggle(this.element, <IOptions>options);

		this.backdrop?.addEventListener('click', () => {
			toggle.closeAllToggles();
		});

		this.element.addEventListener('openMrToggle', () => {
			this.showBackdrop();
		});

		this.element.addEventListener('closeMrToggle', () => {
			this.hideBackdrop();
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

export default HeaderProfile;
