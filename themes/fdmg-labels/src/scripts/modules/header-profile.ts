import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';

class HeaderProfile implements IModule {
	element: HTMLElement;

	constructor(element: HTMLElement) {
		this.element = element;
		this.initialize();
	}

	initialize() {
		const options = {
			useHover: false,
		};

		const toggle = new MrToggle(this.element, <IOptions>options);

		// Close toggle when clicking outside of element
		document.addEventListener('click', (event: Event) => {
			if (!this.element.contains(<HTMLElement>event.target)) {
				toggle.closeAllToggles();
			}
		});
	}
}

export default HeaderProfile;
