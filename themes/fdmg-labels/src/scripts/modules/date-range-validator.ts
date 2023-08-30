import { DateRangeValidator } from '@level-level/recyclops';
import { I18n, IModule } from '../../globals';

declare const i18n: I18n;

class DateRange implements IModule {
	element: HTMLFormElement;
	minDateInput: HTMLInputElement | null;
	maxDateInput: HTMLInputElement | null;
	form: HTMLFormElement | null;

	constructor(element: HTMLFormElement) {
		this.element = element;
		this.form = this.element.closest('form');
		this.minDateInput = this.element.querySelector('.js-min-date-input');
		this.maxDateInput = this.element.querySelector('.js-max-date-input');

		this.initialize();
	}

	initialize() {
		if (!this.form || !this.minDateInput || !this.maxDateInput) {
			return;
		}

		new DateRangeValidator(this.form, {
			minInput: this.minDateInput,
			maxInput: this.maxDateInput,
			errorMessages: {
				summary: i18n.formError,
				minInput: i18n.invalidDateInput,
				maxInput: i18n.invalidDateInput,
			},
			styles: {
				validationSummary: 'relative p-3 pb-0 text-base sm:p-4 sm:pb-0 flex flex-col gap-4',
				validationSummaryTitle: 'font-body-bold',
				validationSummaryList: 'pl-4 list-decimal',
				validationSummaryListItem: '',
				validationSummaryLink: '',
				inputErrorMessage: 'flex items-center gap-2 mt-2',
				inputErrorIcon: 'text-[#c41e3a] text-base',
				inputErrorMessageLabel: 'text-sm',
			},
			submitFormWhenValid: false,
		});
	}
}

export default DateRange;
