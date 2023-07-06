import { ResponsiveHelper } from '@level-level/recyclops';
import { Icons, I18n, IModule } from '../../globals';

declare const icons: Icons;
declare const i18n: I18n;

class PrimaryNav implements IModule {
	element: HTMLElement;
	mainClass: string;
	classNames: {
		parent: string;
		link: string;
		arrow: string;
		sub: string;
	};
	parentItem: HTMLElement[];
	header: HTMLElement;
	responsiveHelper: ResponsiveHelper;

	constructor(element: HTMLElement) {
		this.element = element;
		this.mainClass = 'primary-nav';
		this.classNames = {
			parent: `${this.mainClass}__item--parent`,
			link: `${this.mainClass}__link`,
			arrow: `${this.mainClass}__arrow`,
			sub: `${this.mainClass}-sub`,
		};

		this.parentItem = Array.from(
			element.querySelectorAll(`.${this.classNames.parent}`),
		);
		this.header = <HTMLElement>element.closest('header');
		this.responsiveHelper = new ResponsiveHelper();

		this.initialize();
	}

	initialize() {
		this.createSubmenuButton();
		this.addEventListeners();
	}

	createSubmenuButton() {
		const button = `<button class="${this.classNames.arrow}" aria-expanded="false">
				<span class="icon-inline" aria-hidden="true">${icons.arrowDown}</span>
				<span class="screen-reader-text">${i18n.navigation.subMenu}</span>
			</button>`;

		// Add button before sub menu
		this.parentItem.forEach((el) => {
			el
				.querySelector(`.${this.classNames.sub}`)
				?.insertAdjacentHTML('beforebegin', button);
		});

		// Add aria attribute to all sub-menu's
		this.element.querySelectorAll(`.${this.classNames.sub}`).forEach((el) => {
			el.setAttribute('aria-hidden', 'true');
		});
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
		this.header.addEventListener(
			'keyup',
			this.closeAllWhenNotActive.bind(this),
		);
	}

	determineViewport() {
		// Dropdowns for desktops are using the mouseenter/mouseleave functionality
		if (this.responsiveHelper.isDesktop()) {
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
		const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;

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
		const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;

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
		const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;

		// Show dropdown
		if (currentTarget.getAttribute('aria-expanded') === 'false') {
			currentTarget.setAttribute('aria-expanded', 'true');
			currentTarget.nextElementSibling?.setAttribute('aria-hidden', 'false');

			// Hide dropdown
		} else {
			currentTarget.setAttribute('aria-expanded', 'false');
			currentTarget.nextElementSibling?.setAttribute('aria-hidden', 'true');
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
			const focusedElement: HTMLElement = <HTMLElement>(
				this.element.querySelector(':focus')
			);

			// When tabbing through top level links, close all dropdowns
			if (focusedElement.getAttribute('class') === this.classNames.link) {
				this.element
					.querySelectorAll(`.${this.classNames.sub}`)
					.forEach((el) => {
						el.setAttribute('aria-hidden', 'true');
					});

				this.element
					.querySelectorAll(`.${this.classNames.arrow}`)
					.forEach((el) => {
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
			if (
				!this.element.querySelector(':focus')?.closest(`.${this.mainClass}`)
			) {
				this.closeAll();
			}
		}
	}
}

export default PrimaryNav;
