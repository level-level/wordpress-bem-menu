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
		this.maybeAddStickyClass();
	}

	maybeAddStickyClass() {
		if (!this.sections) {
			return;
		}

		const lastSection = this.sections[this.sections.length - 1] as HTMLElement | null;
		const ad = lastSection?.querySelector('.advertisement');

		if (ad !== null) {
			lastSection?.classList.add('md:sticky', 'md:mb-6', 'top-[calc(theme(space.admin-bar)+theme(space.header-height)+theme(space.6))]');
		}
	}
}

export default StickyAdvertisment;
