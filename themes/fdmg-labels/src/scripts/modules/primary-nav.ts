import { ResponsiveHelper } from '@level-level/recyclops';
import { IModule } from '../../globals';

class PrimaryNav implements IModule {
	element: HTMLElement;
	mainClass: string;
	config: {
		mainClass?: string;
	};
	classNames: {
		parent: string;
		link: string;
		arrow: string;
		sub: string;
	};
	parentItem: HTMLElement[];
	header: HTMLElement | null;
	responsiveHelper: ResponsiveHelper;

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config ? JSON.parse(this.element.dataset.config) : {};
		this.mainClass = this.config.mainClass ? this.config.mainClass : 'primary-nav';
		this.classNames = {
			parent: `${this.mainClass}__item--parent`,
			link: `${this.mainClass}__link`,
			arrow: `${this.mainClass}__arrow`,
			sub: `${this.mainClass}-sub`,
		};

		this.parentItem = Array.from(element.querySelectorAll(`.${this.classNames.parent}`));
		this.header = element.closest('header');
		this.responsiveHelper = new ResponsiveHelper();

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		['load', 'resize', 'orientationchange'].forEach((e) => {
			window.addEventListener(e, this.determineViewport.bind(this));
		});

		// On click on the new dropdown button
		this.element.querySelectorAll(`.${this.classNames.arrow}`).forEach((el) => {
			el.addEventListener('click', this.showMobileDropdown.bind(this));
		});

		this.element.addEventListener('keydown', this.handleKeyboard.bind(this));

		if (!this.config.mainClass) {
			this.header?.addEventListener('keyup', this.closeAllWhenNotActive.bind(this));
		}
	}

	determineViewport() {
		// Dropdowns for desktops are using the mouseenter/mouseleave functionality
		if (this.responsiveHelper.isDesktop() && !this.config.mainClass) {
			this.parentItem.forEach((el) => {
				el.addEventListener('mouseenter', this.showDropdown.bind(this));
				el.addEventListener('mouseleave', this.hideDropdown.bind(this));
			});
		} else {
			this.parentItem.forEach((el) => {
				el.removeEventListener('mouseenter', this.showDropdown.bind(this));
				el.removeEventListener('mouseleave', this.hideDropdown.bind(this));
			});
		}

		// Only on viewports that are atleast the tablet landscape size close dropdowns that are not active
		if (this.responsiveHelper.atLeastTabletLandscape()) {
			this.element.addEventListener('keyup', this.handleTab.bind(this));
		} else {
			this.element.removeEventListener('keyup', this.handleTab.bind(this));
		}
	}

	/**
	 * Show dropdown on mouseenter, set aria logic
	 *
	 * @param  event
	 */
	showDropdown(event: Event) {
		const currentTarget = event.currentTarget as HTMLElement;

		Array.from(currentTarget.children).forEach((el) => {
			if (el.classList.contains(this.classNames.sub)) {
				el.setAttribute('aria-hidden', 'false');
			}

			if (el.classList.contains(this.classNames.arrow)) {
				el.setAttribute('aria-expanded', 'true');
			}
		});
	}

	/**
	 * Hide dropdown on mouseleave, set aria logic
	 *
	 * @param  event
	 */
	hideDropdown(event: Event) {
		const currentTarget = event.currentTarget as HTMLElement;

		Array.from(currentTarget.children).forEach((el) => {
			if (el.classList.contains(this.classNames.sub)) {
				el.setAttribute('aria-hidden', 'true');
			}

			if (el.classList.contains(this.classNames.arrow)) {
				el.setAttribute('aria-expanded', 'false');
			}
		});
	}

	/**
	 * Close all open dropdowns
	 */
	closeAll() {
		this.element.querySelectorAll(`.${this.classNames.sub}`).forEach((el) => {
			el.setAttribute('aria-hidden', 'true');
		});

		this.element.querySelectorAll(`.${this.classNames.arrow}`).forEach((el) => {
			el.setAttribute('aria-expanded', 'false');
		});
	}

	/**
	 * Show and hide dropdowns on click for mobile and tablet portrait
	 *
	 * @param  event
	 */
	showMobileDropdown(event: Event) {
		const currentTarget = event.currentTarget as HTMLElement;

		// Show dropdown
		if (currentTarget.getAttribute('aria-expanded') === 'false') {
			currentTarget.setAttribute('aria-expanded', 'true');
			currentTarget.nextElementSibling?.setAttribute('aria-hidden', 'false');
			currentTarget.closest(`.${this.classNames.parent}`)?.classList.add(`${this.classNames.parent}-is-open`);

			// Hide dropdown
		} else {
			currentTarget.setAttribute('aria-expanded', 'false');
			currentTarget.nextElementSibling?.setAttribute('aria-hidden', 'true');
			currentTarget.closest(`.${this.classNames.parent}`)?.classList.remove(`${this.classNames.parent}-is-open`);
		}
	}

	/**
	 * Close dropdowns with the escape key
	 *
	 * @param  event
	 */
	handleKeyboard(event: KeyboardEvent) {
		// Close dropdowns with the escape key
		if (event.key === 'Escape') {
			this.closeAll();
		}
	}

	/**
	 * Closes dropdowns when tabbing through the navigation
	 *
	 * @param  event
	 */
	handleTab(event: KeyboardEvent) {
		if (event.key === 'Tab') {
			// Current element that is focused
			const focusedElement = this.element.querySelector(':focus') as HTMLElement;

			// When tabbing through top level links, close all dropdowns
			if (focusedElement.getAttribute('class') === this.classNames.link) {
				this.element.querySelectorAll(`.${this.classNames.sub}`).forEach((el) => {
					el.setAttribute('aria-hidden', 'true');
				});

				this.element.querySelectorAll(`.${this.classNames.arrow}`).forEach((el) => {
					el.setAttribute('aria-expanded', 'false');
				});

				// When tabbing through sub menu links, close all sub dropdowns
			} else if (focusedElement.closest(`.${this.classNames.sub}`)) {
				focusedElement
					.closest(`.${this.classNames.sub}`)
					?.querySelectorAll(`.${this.classNames.sub}`)
					.forEach((el) => {
						el.setAttribute('aria-hidden', 'true');
					});

				focusedElement
					.closest(`.${this.classNames.sub}`)
					?.querySelectorAll(`.${this.classNames.arrow}`)
					.forEach((el) => {
						el.setAttribute('aria-expanded', 'false');
					});
			}
		}
	}

	/**
	 * Closes dropdowns when tabbing out of the navigation
	 *
	 * @param  event
	 */
	closeAllWhenNotActive(event: KeyboardEvent) {
		if (event.key === 'Tab' && !event.shiftKey) {
			if (!this.element.querySelector(':focus')?.closest(`.${this.mainClass}`)) {
				this.closeAll();
			}
		}
	}
}

export default PrimaryNav;
