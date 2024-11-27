declare const wpApiSettings: {
	root: string;
	versionString: string;
	nonce: string;
};

declare const acf: any;

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
		this.submit?.addEventListener('click', this.createAuthor.bind(this));
		this.input?.addEventListener('keypress', (event) => {
			if (event.target instanceof HTMLInputElement) {
				this.hideAllMessages();
			}
		});
	}

	createAuthor() {
		const name = this.input?.value;

		if (!name) {
			return;
		}

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
		.then((data: { id: number }) => {
			// If the author was created successfully
			if (data.id) {

				// Clear the input field
				if ( this.input ) {
					this.input.value = '';
				}

				// Update the ACF relationship field
				this.updateACFRelationshipFieldValue(data.id, name);
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

	updateACFRelationshipFieldValue( value: number, label: string ) {
		const field = acf.getField('field_148aff5f'); // The ACF field key
		if ( ! field ) {
			return;
		}

		const choice = field.$el.find( '.acf-relationship .choices .acf-rel-item[data-id="' + value + '"]' );
		if ( choice ) {
			choice.addClass( 'disabled' );
		}

		const html = field.newValue( {
			id: value,
			text: label
		} );
		field.$list( 'values' ).append( html );

		// trigger change
		field.$input().trigger( 'change' );

		this.showSuccessMessage();
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
