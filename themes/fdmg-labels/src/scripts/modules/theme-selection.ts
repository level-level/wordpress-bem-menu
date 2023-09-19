import { IModule } from '../../globals';

class ThemeSelection implements IModule {
	element: HTMLElement;
	radios: HTMLInputElement[] | null;
	html: HTMLElement | null;
	isDarkPreference: MediaQueryList;

	constructor(element: HTMLElement) {
		this.element = element;
		this.radios = Array.from(this.element.querySelectorAll('input[type="radio"]'));
		this.html = document.querySelector('html');
		this.isDarkPreference = window.matchMedia('(prefers-color-scheme: dark)');

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	// Set active theme based on radiobutton selection
	addEventListeners() {
		this.radios?.forEach((radio) => {
			radio.addEventListener('change', () => {
				this.setActiveTheme(radio.value as 'light' | 'dark');
			});
		});

		// Set active theme based on system preference (dark/light)
		this.isDarkPreference.addEventListener('change', (e: MediaQueryListEvent) => {
			if (e.matches) {
				this.setActiveTheme('dark');
			} else {
				this.setActiveTheme('light');
			}
		});
	}

	setActiveTheme(theme: 'light' | 'dark') {
		// Uncheck any checked radiobuttons
		this.radios?.forEach((radio) => {
			radio.checked = false;
		});

		this.html?.setAttribute('data-mode', theme);

		// Check radiobutton with active theme
		document.querySelectorAll<HTMLInputElement>(`[value="${theme}"]`).forEach((radio) => {
			radio.checked = true;
		});

		// Save theme preference in the localStorage
		localStorage.setItem('theme-style', theme);
	}
}

export default ThemeSelection;
