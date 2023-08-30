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
	categoryCheckboxes: NodeListOf<HTMLInputElement> | undefined;
	checkedCategories: string[];
	hiddenCategoryInput: HTMLInputElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.buttons = this.element.querySelectorAll('.js-reset-filter');
		this.checkboxes = this.element.querySelectorAll('input[type="checkbox"]');
		this.periodRadioButtons = this.element.querySelectorAll('input[name="period"]');
		this.minimumDateInput = element.querySelector('input[name="post_date_min"]');
		this.maximumDateInput = element.querySelector('input[name="post_date_max"]');
		this.dateRangeContainer = element.querySelector('.js-date-range');
		this.checkedCategories = [];
		this.categoryCheckboxes = this.element.querySelectorAll('input[name="category[]"]');
		this.hiddenCategoryInput = this.element.querySelector('input[name="category_name"]');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();

		if (this.hiddenCategoryInput && this.categoryCheckboxes) {
			this.populateCheckedCategories();
		}
	}

	addEventListeners() {
		this.buttons?.forEach((button) => {
			button.addEventListener('click', this.resetFilter.bind(this));
		});

		this.periodRadioButtons?.forEach((input) => {
			input.addEventListener('change', this.handleRadioButtonChange.bind(this));
		});

		this.categoryCheckboxes?.forEach((checkbox) => {
			checkbox.addEventListener('change', this.handleCategoryCheckboxChange.bind(this));
		});
	}

	/**
	 * Populate checked categories from the URL
	 */
	populateCheckedCategories() {
		const url = new URL(document.location.href);
		const search = new URLSearchParams(url.search);
		const categoryName = search.get('category_name')?.split(',');
		if (categoryName) {
			this.checkedCategories = categoryName;
		}
	}

	/**
	 * Handle category checkbox change, since we're using a hidden input to store the checked categories we need to update the value of the hidden input.
	 */
	handleCategoryCheckboxChange(event: Event) {
		const targetCheckbox = event.target as HTMLInputElement;

		if (targetCheckbox.checked) {
			// If the checkbox is checked, add its value to the array.
			this.checkedCategories.push(targetCheckbox.value);
		} else {
			// If the checkbox is unchecked, remove its value from the array.
			const index = this.checkedCategories.indexOf(targetCheckbox.value);
			if (index !== -1) {
				this.checkedCategories.splice(index, 1);
			}
		}

		if (this.hiddenCategoryInput) {
			this.hiddenCategoryInput.value = this.checkedCategories.join(',');
		}
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

		// Reset the category checkboxes
		this.checkedCategories = [];
		if (this.hiddenCategoryInput) {
			this.hiddenCategoryInput.value = '';
		}
	}
}

export default FilterForm;
