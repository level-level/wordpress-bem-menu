import { IModule } from '../../globals';

class Advertisement implements IModule {
	element: HTMLElement;
	advertisement: HTMLElement | null;

	constructor(element: HTMLElement) {
		this.element = element;
		this.advertisement = this.element.querySelector('.advertisement');
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		if (!this.advertisement) {
			return;
		}

		// Check for ads on page by checking if an iframe (like google adsense) is present since there is no official way to detect google ads
		const observer = new MutationObserver((mutationsList) => {
			for (const mutation of mutationsList) {
				if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {

					// Loop through added nodes
					for (let i = 0; i < mutation.addedNodes.length; i++) {
						const node = mutation.addedNodes[i];

						// Check if the added node is an iframe
						if (node.nodeName === 'IFRAME') {
							this.element.style.display = 'block';
						} else {
							this.element.style.display = 'none';
						}
					}
				}
			}
		});
		observer.observe(this.advertisement, { childList: true, subtree: true });
	}
}

export default Advertisement;
