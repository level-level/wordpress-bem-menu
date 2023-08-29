import { IModule } from '../../globals';

class NavigationAdvertisement implements IModule {
	element: HTMLElement;
	previousHeight: number;
	interval: any;
	header: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.previousHeight = element.clientHeight;
		this.interval = null;
		this.header = document.querySelector('header');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		// Set the initial top position of the navigation
		this.setNavigationTopPosition(this.element.clientHeight);

		// Check every second if the height has changed
		this.interval = setInterval(this.checkHeightChange.bind(this), 1000);
	}

	// @TODO check if we can check if a ad is loaded
	// Function to check if the height has changed
	checkHeightChange() {
		// Get the current height of the element
		const currentHeight = this.element.clientHeight;

		// Compare it to the previous height
		// Height changed:
		if (currentHeight !== this.previousHeight) {
			// Update the previous height
			this.previousHeight = currentHeight;

			// Set the top position of the navigation
			this.setNavigationTopPosition(this.previousHeight);

			// Stop further checks
			clearInterval(this.interval);
		}
	}

	setNavigationTopPosition(height: number) {
		if (!this.header) {
			return;
		}

		const adminbarHeight =
			parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wp-admin--admin-bar--height')) || 0;

		const top = adminbarHeight - height;

		this.header.style.top = `${top}px`;
	}
}

export default NavigationAdvertisement;
