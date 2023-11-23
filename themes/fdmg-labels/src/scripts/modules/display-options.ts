import { ResponsiveHelper } from '@level-level/recyclops';
import { IModule } from '../../globals';

class DisplayOptions implements IModule {
	element: HTMLElement;
	headerProfile: HTMLElement | null;
	responsiveHelper: ResponsiveHelper;

	constructor(element: HTMLElement) {
		this.element = element;
		this.headerProfile = document.querySelector<HTMLElement>('[class*=js-header-profile]');
		this.responsiveHelper = new ResponsiveHelper();

		this.initialize();
	}

	initialize() {
		this.element.addEventListener('keydown', this.handleKeyboard.bind(this));
	}

	handleKeyboard(event: KeyboardEvent) {
		if (event.key === 'Tab' && this.responsiveHelper.atLeastTabletPortrait()) {
			event.preventDefault();
			this.headerProfile?.focus();
		}
	}
}

export default DisplayOptions;
