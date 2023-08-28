import { IModule } from '../../globals';

class FilterForm implements IModule {
	element: HTMLElement;
	checkboxes: NodeListOf<HTMLInputElement> | undefined;
	buttons: NodeListOf<HTMLButtonElement> | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.buttons = this.element.querySelectorAll('.js-reset-filter');
		this.checkboxes = this.element.querySelectorAll('input[type="checkbox"]');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.buttons?.forEach((button) => {
			button.addEventListener('click', this.resetFilter.bind(this));
		});
	}

	resetFilter(event: Event) {
		event.preventDefault();

		this.checkboxes?.forEach((checkbox) => {
			checkbox.checked = false;
		});
	}
}

export default FilterForm;
