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
		this.observeAdvertisement();
	}

	// Function to check if the height has changed
	checkHeightChange() {
		// Get the current height of the element
		const currentHeight = this.element.clientHeight;

		if (currentHeight === 0) {
			return;
		}

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

		const adminbarHeight = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--wp-admin--admin-bar--height')) || 0;

		const top = adminbarHeight - height;

		this.header.style.top = `${top}px`;
	}

	observeAdvertisement() {
		const observer = new MutationObserver((mutationsList) => {
			for (const mutation of mutationsList) {
				if (mutation.type !== 'childList' && mutation.addedNodes.length === 0) {
					return;
				}
				mutation.addedNodes.forEach((node) => {
					// Check if the added node is an iframe
					if (node.nodeName !== 'IFRAME') {
						return;
					}


					// Set the initial top position of the navigation
					this.setNavigationTopPosition(this.element.clientHeight);

					// Check every second if the height has changed
					this.interval = setInterval(this.checkHeightChange.bind(this), 1000);
				});
			}
		});

		observer.observe(this.element, { childList: true, subtree: true });
	}
}

export default NavigationAdvertisement;
