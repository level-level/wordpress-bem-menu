import { IModule } from '../../globals';
import * as dayjs from 'dayjs';

class FilterForm implements IModule {
	element: HTMLElement;
	form: HTMLFormElement | null;
	checkboxes: NodeListOf<HTMLInputElement> | undefined;
	orderBySelect: HTMLSelectElement | null;
	periodRadioButtons: NodeListOf<HTMLInputElement> | undefined;
	buttons: NodeListOf<HTMLButtonElement> | null;
	minimumDateInput: HTMLInputElement | null;
	maximumDateInput: HTMLInputElement | null;
	dateRangeContainer: HTMLElement | null;
	categoryCheckboxes: NodeListOf<HTMLInputElement> | undefined;
	checkedCategories: string[];
	hiddenCategoryInput: HTMLInputElement | null;

	sectionCheckboxes: NodeListOf<HTMLInputElement> | undefined;
	checkedSections: string[];
	hiddenSectionInputs: NodeListOf<HTMLInputElement> | null;

	genreCheckboxes: NodeListOf<HTMLInputElement> | undefined;
	checkedGenres: string[];
	hiddenGenreInputs: NodeListOf<HTMLInputElement> | null;

	authorCheckboxes: NodeListOf<HTMLInputElement> | undefined;
	checkedAuthors: string[];

	constructor(element: HTMLElement) {
		this.element = element;
		this.form = this.element.querySelector('form');
		this.buttons = this.element.querySelectorAll('.js-reset-filter');
		this.checkboxes = this.element.querySelectorAll('input[type="checkbox"]');
		this.orderBySelect = this.element.querySelector('select[name="orderby"]');
		this.periodRadioButtons = this.element.querySelectorAll('input[name="period"]');
		this.minimumDateInput = element.querySelector('input[name="post_date_min"]');
		this.maximumDateInput = element.querySelector('input[name="post_date_max"]');
		this.dateRangeContainer = element.querySelector('.js-date-range');

		this.checkedCategories = [];
		this.categoryCheckboxes = this.element.querySelectorAll('input[name="category[]"]');
		this.hiddenCategoryInput = document.querySelector('input[type="hidden"][name="category_name"]');

		this.checkedSections = [];
		this.sectionCheckboxes = this.element.querySelectorAll('input[name="fdmg_section[]"]');
		this.hiddenSectionInputs = document.querySelectorAll('input[type="hidden"][name="fdmg_section"]');

		this.checkedGenres = [];
		this.genreCheckboxes = this.element.querySelectorAll('input[name="fdmg_genre[]"]');
		this.hiddenGenreInputs = document.querySelectorAll('input[type="hidden"][name="fdmg_genre"]');

		this.checkedAuthors = [];
		this.authorCheckboxes = this.element.querySelectorAll('input[name="author_id[]"]');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();

		if (this.hiddenCategoryInput && this.categoryCheckboxes) {
			this.populateCheckedCategories();
		}

		if (this.hiddenSectionInputs && this.sectionCheckboxes) {
			this.populateCheckedSections();
		}

		if (this.hiddenGenreInputs && this.genreCheckboxes) {
			this.populateCheckedGenres();
		}

		if (this.authorCheckboxes) {
			this.populateCheckedAuthors();
		}
	}

	addEventListeners() {
		this.buttons?.forEach((button) => {
			button.addEventListener('click', this.resetFilter.bind(this));
		});

		this.periodRadioButtons?.forEach((input) => {
			input.addEventListener('change', this.handleRadioButtonChange.bind(this));
		});

		this.orderBySelect?.addEventListener('change', this.handleSortByChange.bind(this));

		this.categoryCheckboxes?.forEach((checkbox) => {
			checkbox.addEventListener('change', this.handleCategoryCheckboxChange.bind(this));
		});

		this.sectionCheckboxes?.forEach((checkbox) => {
			checkbox.addEventListener('change', this.handleSectionCheckboxChange.bind(this));
		});

		this.genreCheckboxes?.forEach((checkbox) => {
			checkbox.addEventListener('change', this.handleGenreCheckboxChange.bind(this));
		});

		this.authorCheckboxes?.forEach((checkbox) => {
			checkbox.addEventListener('change', this.handleAuthorCheckboxChange.bind(this));
		});

		this.form?.addEventListener('submit', (event) => {
			event.preventDefault();

			// When form is submitted, disable the category checkboxes and submit the form.
			document.addEventListener('DateRangeValidator:valid', () => {
				this.categoryCheckboxes?.forEach((checkbox) => {
					checkbox.disabled = true;
				});
				this.form?.submit();
			});
		});
	}

	handleSortByChange(event: Event) {
		const selectedSort = event.target as HTMLSelectElement;
		const hiddenSortInput: NodeListOf<HTMLInputElement> | undefined = document.querySelectorAll('input[type="hidden"][name="orderby"]');
		if (hiddenSortInput) {
			hiddenSortInput.forEach((input) => {
				input.value = selectedSort.value;
			});
		}
	}

	/**
	 * Populate checked categories from the URL
	 */
	populateCheckedCategories() {
		const url = new URL(document.location.href);
		const search = new URLSearchParams(url.search);
		const name = search.get('category_name');
		if (name) {
			this.checkedCategories = name.split(',');
		}
	}

	/**
	 * Populate checked sections from the URL
	 */
	populateCheckedSections() {
		const url = new URL(document.location.href);
		const search = new URLSearchParams(url.search);
		const name = search.get('fdmg_section');
		if (name) {
			this.checkedSections = name.split(',');
		}
	}

	/**
	 * Populate checked genres from the URL
	 */
	populateCheckedGenres() {
		const url = new URL(document.location.href);
		const search = new URLSearchParams(url.search);
		const name = search.get('fdmg_genre');
		if (name) {
			this.checkedGenres = name.split(',');
		}
	}

	/**
	 * Populate checked authors from the URL
	 */
	populateCheckedAuthors() {
		const url = new URL(document.location.href);
		const search = new URLSearchParams(url.search);
		const authorId = this.getArrayParam('author_id', search);
		if (authorId.length > 0) {
			this.checkedAuthors = authorId;

			// Create hidden inputs for each checked author
			this.createHiddenAuthorInputs();
		}
	}

	getArrayParam(param: string, searchParams: URLSearchParams): string[] {
		const values: string[] = [];
		searchParams.forEach((value, key) => {
			if (key.startsWith(param)) {
				values.push(decodeURIComponent(value));
			}
		});
		return values;
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

	handleSectionCheckboxChange(event: Event) {
		const targetCheckbox = event.target as HTMLInputElement;

		if (targetCheckbox.checked) {
			// If the checkbox is checked, add its value to the array.
			this.checkedSections.push(targetCheckbox.value);
		} else {
			// If the checkbox is unchecked, remove its value from the array.
			const index = this.checkedSections.indexOf(targetCheckbox.value);
			if (index !== -1) {
				this.checkedSections.splice(index, 1);
			}
		}

		if (this.hiddenSectionInputs) {
			this.hiddenSectionInputs.forEach((input) => {
				input.value = this.checkedSections.join(',');
			});
		}
	}

	handleGenreCheckboxChange(event: Event) {
		const targetCheckbox = event.target as HTMLInputElement;

		if (targetCheckbox.checked) {
			// If the checkbox is checked, add its value to the array.
			this.checkedGenres.push(targetCheckbox.value);
		} else {
			// If the checkbox is unchecked, remove its value from the array.
			const index = this.checkedGenres.indexOf(targetCheckbox.value);
			if (index !== -1) {
				this.checkedGenres.splice(index, 1);
			}
		}

		if (this.hiddenGenreInputs) {
			this.hiddenGenreInputs.forEach((input) => {
				input.value = this.checkedGenres.join(',');
			});
		}
	}

	/**
	 * The authors work a bit different than the other checkboxes, we need to actually create hidden inputs for each checked author.
	 */
	handleAuthorCheckboxChange(event: Event) {
		const targetCheckbox = event.target as HTMLInputElement;

		if (targetCheckbox.checked) {
			// If the checkbox is checked, add its value to the array.
			this.checkedAuthors.push(targetCheckbox.value);
		} else {
			// If the checkbox is unchecked, remove its value from the array.
			const index = this.checkedAuthors.indexOf(targetCheckbox.value);
			if (index !== -1) {
				this.checkedAuthors.splice(index, 1);
			}
		}

		this.createHiddenAuthorInputs();
	}

	createHiddenAuthorInputs() {
		// Clear existing hidden inputs
		const hiddenInputsContainer = document.querySelectorAll('.js-author-inputs');
		hiddenInputsContainer.forEach((container) => {
			container.innerHTML = '';
		});

		// Create new hidden inputs for each value in checkedAuthors
		this.checkedAuthors.forEach((authorId) => {
			const hiddenInput = document.createElement('input');
			hiddenInput.type = 'hidden';
			hiddenInput.name = 'author_id[]';
			hiddenInput.value = authorId;
			hiddenInputsContainer.forEach((container) => {
				container.appendChild(hiddenInput);
			});
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

		this.setMinimumDateInputValue(target.value);
	}

	/**
	 * Set the minimum date input value based on the selected period
	 */
	setMinimumDateInputValue(value: string) {
		if (!this.minimumDateInput) {
			return;
		}

		if (value === 'week') {
			this.minimumDateInput.value = dayjs().subtract(1, 'week').format('YYYY-MM-DD');
		}

		if (value === 'month') {
			this.minimumDateInput.value = dayjs().subtract(1, 'month').format('YYYY-MM-DD');
		}

		if (value === 'year') {
			this.minimumDateInput.value = dayjs().subtract(1, 'year').format('YYYY-MM-DD');
		}

		if (value === '3-years') {
			this.minimumDateInput.value = dayjs().subtract(3, 'year').format('YYYY-MM-DD');
		}

		if (value === '2000-now') {
			this.minimumDateInput.value = dayjs('2000-01-01').format('YYYY-MM-DD');
		}
	}

	resetFilter(event: Event) {
		event.preventDefault();

		// Reset ordering to relevance
		if (this.orderBySelect) {
			this.orderBySelect.value = 'relevance';
		}

		this.checkboxes?.forEach((checkbox) => {
			checkbox.checked = false;
		});

		// Reset the period radio buttons to the default 3-years filter
		this.periodRadioButtons?.forEach((radioButton) => {
			if (radioButton.value === '3-years') {
				radioButton.checked = true;
				this.setMinimumDateInputValue('3-years');
			}
		});

		// Reset the category checkboxes
		this.checkedCategories = [];
		if (this.hiddenCategoryInput) {
			this.hiddenCategoryInput.value = '';
		}

		this.form?.submit();
	}
}

export default FilterForm;
