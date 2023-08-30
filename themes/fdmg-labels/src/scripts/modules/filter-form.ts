import { IModule } from '../../globals';
import * as dayjs from 'dayjs';

class FilterForm implements IModule {
	element: HTMLElement;
	checkboxes: NodeListOf<HTMLInputElement> | undefined;
	periodRadioButtons: NodeListOf<HTMLInputElement> | undefined;
	buttons: NodeListOf<HTMLButtonElement> | null;
	minimumDateInput: HTMLInputElement | null;
	maximumDateInput: HTMLInputElement | null;
	dateRangeContainer: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.buttons = this.element.querySelectorAll('.js-reset-filter');
		this.checkboxes = this.element.querySelectorAll('input[type="checkbox"]');
		this.periodRadioButtons = this.element.querySelectorAll('input[name="period"]');
		this.minimumDateInput = element.querySelector('input[name="post_date_min"]');
		this.maximumDateInput = element.querySelector('input[name="post_date_max"]');
		this.dateRangeContainer = element.querySelector('.js-date-range');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.buttons?.forEach((button) => {
			button.addEventListener('click', this.resetFilter.bind(this));
		});

		this.periodRadioButtons?.forEach((input) => {
			input.addEventListener('change', this.handleRadioButtonChange.bind(this));
		});
	}

	handleRadioButtonChange(event: Event) {
		if (!this.minimumDateInput || !this.maximumDateInput) {
			return;
		}

		// Resets the date range inputs
		this.minimumDateInput.value = '';
		this.maximumDateInput.value = '';

		const target = event.currentTarget as HTMLInputElement;

		// Toggle visibility on the date range inputs
		if (target.value === 'custom-period') {
			this.dateRangeContainer?.classList.remove('hidden');
			this.dateRangeContainer?.classList.add('block');
		} else {
			this.dateRangeContainer?.classList.remove('block');
			this.dateRangeContainer?.classList.add('hidden');
		}

		// Update minimum date inputs
		if (target.value === 'week') {
			this.minimumDateInput.value = dayjs().subtract(1, 'week').format('YYYY-MM-DD');
		}

		if (target.value === 'month') {
			this.minimumDateInput.value = dayjs().subtract(1, 'month').format('YYYY-MM-DD');
		}

		if (target.value === 'year') {
			this.minimumDateInput.value = dayjs().subtract(1, 'year').format('YYYY-MM-DD');
		}

		if (target.value === '3-years') {
			this.minimumDateInput.value = dayjs().subtract(3, 'year').format('YYYY-MM-DD');
		}

		if (target.value === '2000-now') {
			this.minimumDateInput.value = dayjs('2000-01-01').format('YYYY-MM-DD');
		}
	}

	resetFilter(event: Event) {
		event.preventDefault();

		this.checkboxes?.forEach((checkbox) => {
			checkbox.checked = false;
		});
	}
}

export default FilterForm;
