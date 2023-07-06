import { IModule } from '../../globals';

class StickyAdvertisment implements IModule {
	element: HTMLElement;
	sections: HTMLElement[] | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.sections = Array.from(this.element.querySelectorAll('.section'));
		this.initialize();
	}

	initialize() {
		this.addStickyClass();
	}

	addStickyClass() {
		if (!this.sections) {
			return;
		}

		this.sections.map((item: HTMLElement) => {
			const ad = item.querySelector('.advertisement');
			if (ad !== null) {
				item.classList.add('section--has-advertisement');
			}
		});
	}
}

export default StickyAdvertisment;
