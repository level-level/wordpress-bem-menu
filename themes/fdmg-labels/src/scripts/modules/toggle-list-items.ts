import { IModule } from '../../globals';

class ToggleListItems implements IModule {
	element: HTMLElement;
	config: {
		default: string;
		expanded: string;
	};
	button: HTMLButtonElement | null;
	checkboxes: HTMLInputElement[] | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config ? JSON.parse(this.element.dataset.config) : {};
		this.button = element.querySelector('.js-toggle-list');
		this.checkboxes = Array.from(element.querySelectorAll('input'));

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
		this.getCheckboxStatus();
	}

	addEventListeners() {
		this.button?.addEventListener('click', this.handleClick.bind(this));
	}

	handleClick(event: Event) {
		event.preventDefault();

		if (this.button?.getAttribute('aria-expanded') === 'false') {
			this.showCheckboxes();
		} else {
			this.hideCheckboxes();
		}
	}

	showCheckboxes() {
		if (!this.button) {
			return;
		}

		this.button.setAttribute('aria-expanded', 'true');
		this.button.textContent = this.config.expanded;

		this.checkboxes?.forEach((checkbox) => {
			if (checkbox.parentElement?.classList.contains('js-hidden-item')) {
				checkbox.parentElement?.classList.remove('hidden');
			}
		});
	}

	hideCheckboxes() {
		if (!this.button) {
			return;
		}

		this.button.setAttribute('aria-expanded', 'false');
		this.button.textContent = this.config.default;

		this.checkboxes?.forEach((checkbox) => {
			if (checkbox.parentElement?.classList.contains('js-hidden-item')) {
				checkbox.parentElement?.classList.add('hidden');
			}
		});
	}

	getCheckboxStatus() {
		this.checkboxes?.forEach((checkbox) => {
			if (checkbox.checked) {
				checkbox.parentElement?.classList.remove('hidden');
				checkbox.parentElement?.classList.remove('js-hidden-item');
			}
		});
	}
}

export default ToggleListItems;
