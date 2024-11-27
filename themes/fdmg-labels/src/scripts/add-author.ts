declare const wpApiSettings: {
	root: string;
	versionString: string;
	nonce: string;
};

class AddAuthor {
	element: HTMLElement | null;
	input: HTMLInputElement | null | undefined;
	submit: HTMLButtonElement | null | undefined;
	successMessage: HTMLElement | null | undefined;
	errorMessage: HTMLElement | null | undefined;

	constructor() {
		this.element = document.querySelector('#add-author');
		this.input = this.element?.querySelector('input[id="author-name"]');
		this.submit = this.element?.querySelector('button[type="submit"]');
		this.successMessage = this.element?.querySelector('.js-success');
		this.errorMessage = this.element?.querySelector('.js-error');

		this.addEventListeners();
	}

	addEventListeners() {
		this.submit?.addEventListener('click', this.postAuthor.bind(this));
		this.input?.addEventListener('keypress', (event) => {
			if (event.target instanceof HTMLInputElement) {
				this.hideAllMessages();
			}
		});
	}

	postAuthor() {
		const name = this.input?.value;

		this.hideAllMessages();

		fetch(`${wpApiSettings.root}${wpApiSettings.versionString}ii_team`, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/json',
				'X-WP-Nonce': wpApiSettings.nonce,
			},
			body: JSON.stringify({
				title: name,
				status: 'publish',
			})
		})
		.then((response) => response.json())
		.then((data) => {

			// If the author was created successfully
			if (data.id) {
				if ( this.input ) {
					this.input.value = '';
					this.showSuccessMessage();
				}
			} else {
				console.error('Error creating post:', data); // eslint-disable-line
				this.showErrorMessage();
			}
		})
		.catch((error) => {
			console.error('Error:', error); // eslint-disable-line
			this.showErrorMessage();
		});
	}

	showSuccessMessage() {
		this.successMessage?.classList.remove('hidden');
		this.errorMessage?.classList.add('hidden');
	}

	showErrorMessage() {
		this.errorMessage?.classList.remove('hidden');
		this.successMessage?.classList.add('hidden');
	}

	hideAllMessages() {
		this.successMessage?.classList.add('hidden');
		this.errorMessage?.classList.add('hidden');
	}
}

new AddAuthor();
