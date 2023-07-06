import { IModule } from '../../globals';

class Skiplink implements IModule {
	element: HTMLElement;

	constructor(element: HTMLElement) {
		this.element = element;
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.element.addEventListener('click', (event: Event) => {
			const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;
			const target: HTMLElement | null = document.querySelector(`${currentTarget?.getAttribute('href')}`);

			if (!target) {
				return;
			}

			setTimeout(() => {
				scroll({
					top: target.offsetTop,
					behavior: 'smooth',
				});
			}, 20);
		});
	}
}

export default Skiplink;
