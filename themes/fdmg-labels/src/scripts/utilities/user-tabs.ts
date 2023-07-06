class UserTabs {
	constructor() {
		this.initialize();
	}

	initialize() {
		this.addEventsListener();
	}

	addEventsListener() {
		window.addEventListener('keydown', this.handleFirstTab.bind(this));
	}

	handleFirstTab(event: KeyboardEvent) {
		if (event.key === 'Tab') {
			document.querySelector('body')?.classList.add('user-is-tabbing');
			window.removeEventListener('keydown', this.handleFirstTab.bind(this));
		}
	}
}

const instance = new UserTabs();
export default instance;
