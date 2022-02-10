import { IModule } from '../../globals';

class ThemeSelection implements IModule {
	element: HTMLElement;
	radios: HTMLInputElement[] | null;
	html: HTMLElement | null;

	constructor( element: HTMLElement ) {
		this.element = element;
		this.radios = Array.from( this.element.querySelectorAll( 'input[type="radio"]' ) );
		this.html = document.querySelector( 'html' );

		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	// Set active theme based on radiobutton selection
	addEventListeners() {
		this.radios?.forEach( radio => {
			radio.addEventListener( 'change', () => {
				this.setActiveTheme( radio.value );
			} );
		} );
	}

	setActiveTheme( theme: string ) {

		// Reset all theme classes
		this.html?.classList.remove( 'theme-light' );
		this.html?.classList.remove( 'theme-dark' );
		this.html?.classList.remove( 'theme-system' );

		// Uncheck any checked radiobuttons
		this.radios?.forEach( radio => {
			radio.checked = false;
		} );

		// Add body classes based on theme
		if ( theme === 'light' ) {
			this.html?.classList.add( 'theme-light' );
		} else if ( theme === 'dark' ) {
			this.html?.classList.add( 'theme-dark' );
		} else if ( theme === 'system' ) {
			this.html?.classList.add( 'theme-system' );
		}

		// Check radiobutton with active theme
		// $( `:radio[value="${ theme }"]` ).prop( 'checked', true );
		document.querySelectorAll<HTMLInputElement>( `[value="${ theme }"]` ).forEach( radio => {
			radio.checked = true;
		} );

		// Save theme preference in the localStorage
		localStorage.setItem( 'theme-style', theme );
	}
}

export default ThemeSelection;
