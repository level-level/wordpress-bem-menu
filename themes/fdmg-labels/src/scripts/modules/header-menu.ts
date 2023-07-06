import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';

class HeaderMenu implements IModule {
	element: HTMLElement;
	backdrop: HTMLElement | null;
	html: HTMLElement | null;
	dropdown: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.backdrop = this.element.querySelector('.header-menu__backdrop');
		this.html = document.querySelector('html');
		this.dropdown = this.element.querySelector('.header-menu__dropdown');

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

		this.element.addEventListener('openMrToggle', () => {
			this.showBackdrop();

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

			// Remove style property that fixes iOS Safari 15 issue
			this.html?.removeAttribute('style');
		});
	}

	showBackdrop() {
		this.backdrop?.classList.add('header-menu__backdrop--active');
	}

	hideBackdrop() {
		this.backdrop?.classList.remove('header-menu__backdrop--active');
	}
}

export default HeaderMenu;
