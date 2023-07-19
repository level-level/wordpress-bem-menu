import { IModule } from '../../globals';

class Tabs implements IModule {
	element: HTMLElement;
	tabLinks: HTMLElement[];
	tabSections: HTMLElement[];

	constructor(element: HTMLElement) {
		this.element = element;
		this.tabLinks = Array.from(this.element.querySelectorAll('.js-link'));
		this.tabSections = Array.from(this.element.querySelectorAll('.js-section'));

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.tabLinks.forEach((el) => {
			el.addEventListener('click', this.handleClick.bind(this));
			el.addEventListener('keydown', this.handleKeyboard.bind(this));
		});
	}

	handleClick(event: Event) {
		event.preventDefault();

		const target = <HTMLElement>event.currentTarget;

		if (target?.getAttribute('aria-selected') !== 'true') {
			const currentTab = this.element.querySelector<HTMLElement>('[aria-selected]');
			if (target !== currentTab) {
				this.switchTab(currentTab, target);
			}
		}
	}

	handleKeyboard(event: KeyboardEvent) {
		const target = <HTMLElement>event.currentTarget;

		if (event.key === 'ArrowLeft') {
			const previousTab = target?.parentElement?.previousElementSibling;

			if (previousTab?.classList.contains('js-item')) {
				target.blur();
				previousTab?.querySelector<HTMLElement>('.js-link')?.focus();
				this.switchTab(target, previousTab.querySelector('.js-link'));
			}
		}

		if (event.key === 'ArrowRight') {
			const nextTab = target?.parentElement?.nextElementSibling;

			if (nextTab?.classList.contains('js-item')) {
				target.blur();
				nextTab?.querySelector<HTMLElement>('.js-link')?.focus();
				this.switchTab(target, nextTab.querySelector('.js-link'));
			}
		}
	}

	switchTab(oldTab: HTMLElement | null, newTab: HTMLElement | null) {
		if (!newTab || !oldTab) {
			return;
		}

		newTab.focus();
		newTab.setAttribute('aria-selected', 'true');
		newTab.setAttribute('tabindex', '0');
		oldTab.removeAttribute('aria-selected');
		oldTab.setAttribute('tabindex', '-1');

		const current = newTab.getAttribute('href');

		this.tabSections.forEach((el) => {
			el.setAttribute('aria-hidden', 'true');

			if (`#${el.getAttribute('id')}` === current) {
				el.setAttribute('aria-hidden', 'false');
			}
		});
	}
}

export default Tabs;
