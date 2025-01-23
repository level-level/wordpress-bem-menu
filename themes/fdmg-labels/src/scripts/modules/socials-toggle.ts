import { MrToggle } from '@level-level/recyclops';
import { IModule } from '../../globals';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';

class SocialsToggle implements IModule {
	element: HTMLElement;

	constructor(element: HTMLElement) {
		this.element = element;

		this.initialize();
	}

	initialize() {
		const options: IOptions = {
			moduleName: 'toggle',
			useHover: false,
		};

		new MrToggle(this.element, options);
	}
}

export default SocialsToggle;
