import { IModule } from '../../globals';

class Example implements IModule {
	element: HTMLElement;
	config: {
		greetings: string[];
		clicked: string;
	};
	randomGreeting: HTMLElement | null;
	random: string;

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config ? JSON.parse(this.element.dataset.config) : {};
		this.randomGreeting = this.element.querySelector('.random-greeting');
		this.random = '';
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		this.element.addEventListener('click', (event: Event) => {
			const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;

			if (currentTarget.matches('button')) {
				const buttonText = currentTarget.querySelector('.btn__text');

				if (!buttonText) {
					return;
				}
				buttonText.textContent = this.config.clicked;
			}
		});

		setInterval(() => {
			if (this.config?.greetings) {
				if (!this.randomGreeting) {
					return;
				}

				this.random = this.config.greetings[Math.floor(Math.random() * this.config.greetings.length)];
				this.randomGreeting.textContent = this.random;
			}
		}, 500);
	}
}

export default Example;
