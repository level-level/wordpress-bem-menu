import { clearAllBodyScrollLocks, disableBodyScroll, enableBodyScroll } from 'body-scroll-lock';
import { IModule } from '../../globals';
import { ResponsiveHelper, TabFocusHelper, getFirstFocusableElement } from '@level-level/recyclops';

class FilterModal implements IModule {
	element: HTMLElement;
	modal: HTMLDivElement | null;
	backdrop: HTMLDivElement | null;
	modalFilters: HTMLDivElement | null | undefined;
	tabFocus: TabFocusHelper;
	closeButton: HTMLButtonElement | null | undefined;
	responsiveHelper: ResponsiveHelper;

	constructor(element: HTMLElement) {
		this.element = element;
		this.modal = document.querySelector('.js-filter-modal');
		this.backdrop = document.querySelector('.js-modal-backdrop');
		this.modalFilters = this.modal?.querySelector('.js-modal-body');
		this.closeButton = this.modal?.querySelector('.js-close-modal');
		this.tabFocus = new TabFocusHelper();
		this.responsiveHelper = new ResponsiveHelper();

		this.initialize();
	}

	initialize() {
		if (!this.modal) {
			return;
		}

		this.addEventListeners();
	}

	addEventListeners() {
		if (!this.modal) {
			return;
		}

		['load', 'resize', 'orientationchange'].forEach((e) => {
			window.addEventListener(e, this.determineViewport.bind(this));
		});

		this.element.addEventListener('click', this.showModal.bind(this));
		this.backdrop?.addEventListener('click', this.closeModal.bind(this));
		this.closeButton?.addEventListener('click', this.closeModal.bind(this));
		this.modal.addEventListener('keydown', this.handleKeyboard.bind(this));
	}

	determineViewport() {
		if (this.responsiveHelper.atLeastTabletLandscape()) {
			this.modal?.removeAttribute('aria-hidden');
			this.backdrop?.setAttribute('aria-hidden', 'true');
			clearAllBodyScrollLocks();
		} else {
			this.modal?.setAttribute('aria-hidden', 'true');
			this.backdrop?.setAttribute('aria-hidden', 'true');
			this.element?.setAttribute('aria-expanded', 'false');
		}
	}

	showModal() {
		if (!this.modalFilters) {
			return;
		}

		this.element.setAttribute('aria-expanded', 'true');
		this.modal?.setAttribute('aria-hidden', 'false');
		this.backdrop?.setAttribute('aria-hidden', 'false');
		disableBodyScroll(this.modalFilters);

		setTimeout(() => {
			if (!this.modal) {
				return;
			}
			getFirstFocusableElement(this.modal)?.focus();
		}, 100);
	}

	closeModal(event?: Event) {
		event?.preventDefault();

		if (!this.modal || !this.modalFilters) {
			return;
		}

		this.element.setAttribute('aria-expanded', 'false');
		this.modal?.setAttribute('aria-hidden', 'true');
		this.backdrop?.setAttribute('aria-hidden', 'true');
		enableBodyScroll(this.modalFilters);

		setTimeout(() => {
			this.element.focus();
		}, 100);
	}

	handleKeyboard(event: KeyboardEvent) {
		if (!this.modal) {
			return;
		}

		if (event.key === 'Escape') {
			this.closeModal();
		}

		if (event.key === 'Tab') {
			if (this.element?.getAttribute('aria-expanded') === 'true') {
				this.tabFocus.trap(this.modal, event);
			}
		}
	}
}

export default FilterModal;
