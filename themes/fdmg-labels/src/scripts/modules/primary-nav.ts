import { IModule } from '../../globals';

class PrimaryNav implements IModule {
	element: HTMLElement;
	mainClass: string;
	config: {
		mainClass?: string;
	};
	classNames: {
		parent: string;
		toggle: string;
		submenu: string;
	};
	parentItems: HTMLElement[];
	toggles: HTMLButtonElement[];
	header: HTMLElement | null;
	expandedItem: {
		toggle: HTMLButtonElement | null;
		submenu: HTMLElement | null;
	};
	desktopBreakpoint: number;

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config ? JSON.parse(this.element.dataset.config) : {};
		this.mainClass = this.config.mainClass ? this.config.mainClass : 'primary-nav';
		this.classNames = {
			parent: `${this.mainClass}__item--parent`,
			toggle: `${this.mainClass}__toggle`,
			submenu: `${this.mainClass}-sub`,
		};

		this.parentItems = Array.from(element.querySelectorAll(`.${this.classNames.parent}`));
		this.toggles = Array.from(element.querySelectorAll(`.${this.classNames.toggle}`));
		this.header = element.closest('header');
		this.expandedItem = {
			toggle: null,
			submenu: null,
		};
		this.desktopBreakpoint = 1024;

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.parentItems.forEach((el) => {
			el.addEventListener('mouseenter', (e) => this.handleMouseEnter(e));
			el.addEventListener('mouseleave', (e) => this.handleMouseLeave(e));
		});

		this.toggles.forEach((el) => {
			el.addEventListener('click', (e) => this.handleClick(e));
		});

		document.addEventListener('keydown', (e) => this.handleKeyDown(e));
	}

	handleMouseEnter(event: Event) {
		if (window.innerWidth < this.desktopBreakpoint) {
			return;
		}

		const target = event.currentTarget as HTMLElement;
		const toggle = target.querySelector(`.${this.classNames.toggle}`) as HTMLButtonElement;
		const submenu = target.querySelector(`.${this.classNames.submenu}`) as HTMLElement;

		this.openDropdown(toggle, submenu);
	}

	handleMouseLeave(event: Event) {
		if (window.innerWidth < this.desktopBreakpoint) {
			return;
		}

		const target = event.currentTarget as HTMLElement;
		const toggle = target.querySelector(`.${this.classNames.toggle}`) as HTMLButtonElement;
		const submenu = target.querySelector(`.${this.classNames.submenu}`) as HTMLElement;
		const parent = toggle.closest(`.${this.classNames.parent}`) as HTMLElement;
		const parentSubmenu = parent.closest(`.${this.classNames.submenu}`) as HTMLElement;

		if (parentSubmenu) {
			return;
		}

		this.closeDropdown(toggle, submenu);
	}

	handleClick(event: Event) {
		const toggle = event.currentTarget as HTMLButtonElement;
		const submenu = toggle.nextElementSibling as HTMLElement;

		if (toggle.getAttribute('aria-expanded') === 'true') {
			this.closeDropdown(toggle, submenu);
		} else {
			this.openDropdown(toggle, submenu);
		}
	}

	closeDropdown(toggle: HTMLButtonElement, submenu: HTMLElement) {
		const parent = toggle.closest(`.${this.classNames.parent}`) as HTMLElement;
		const parentSubmenu = parent.closest(`.${this.classNames.submenu}`) as HTMLElement;

		toggle.setAttribute('aria-expanded', 'false');
		submenu.setAttribute('aria-hidden', 'true');
		parent.removeAttribute('open');

		this.expandedItem = {
			toggle: null,
			submenu: null,
		};
	}

	openDropdown(toggle: HTMLButtonElement, submenu: HTMLElement) {
		const parent = toggle.closest(`.${this.classNames.parent}`) as HTMLElement;

		submenu.setAttribute('aria-hidden', 'false');
		toggle.setAttribute('aria-expanded', 'true');
		parent.setAttribute('open', '');

		this.expandedItem = {
			toggle,
			submenu,
		};
	}

	handleKeyDown(event: KeyboardEvent) {
		if (!this.expandedItem.toggle || !this.expandedItem.submenu) {
			return;
		}

		const parent = this.expandedItem.toggle.closest(`.${this.classNames.parent}`) as HTMLElement;
		const toggle = this.expandedItem.toggle;
		const submenu = this.expandedItem.submenu;

		if (event.key === 'Tab') {
			const focusedElement = parent.querySelector(':focus');
			const focusableElements = parent.querySelectorAll('a');
			const firstFocusableElement = focusableElements[0];
			const lastFocusableElement = focusableElements[focusableElements.length - 1];

			if (!event.shiftKey && focusedElement === lastFocusableElement) {
				this.closeDropdown(toggle, submenu);
				return;
			}

			if (event.shiftKey && focusedElement === firstFocusableElement) {
				this.closeDropdown(toggle, submenu);
				return;
			}
		}

		if (event.key == 'Escape') {
			this.closeDropdown(toggle, submenu);
		}
	}
}

export default PrimaryNav;
