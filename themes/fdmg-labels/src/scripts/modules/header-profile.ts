import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';

class HeaderProfile implements IModule {
	element: HTMLElement;
	backdrop: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.backdrop = this.element.querySelector('.js-header-profile-backdrop');
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
	}

	showBackdrop() {
		this.backdrop?.setAttribute('aria-hidden', 'false');
	}

	hideBackdrop() {
		this.backdrop?.setAttribute('aria-hidden', 'true');
	}
}

export default HeaderProfile;
