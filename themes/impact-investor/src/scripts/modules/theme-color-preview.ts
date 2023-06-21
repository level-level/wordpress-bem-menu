import { IModule } from '../../globals';

class ThemeColorPreview implements IModule {
	element: HTMLElement;
	buttons: HTMLButtonElement[];
	pattern: string[];
	current: number;

	constructor( element: HTMLElement ) {
		this.element = element;
		this.buttons = Array.from( this.element.querySelectorAll( 'button' ) );
		this.pattern = ['g', 'o', 'd', 'm', 'o', 'd', 'e'];
		this.current = 0;
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
		document.addEventListener( 'keydown', this.keyHandler.bind( this ) );
	}

	keyHandler( event: KeyboardEvent ) {

		// If the key isn't in the pattern, or isn't the current key in the pattern, reset
		if ( this.pattern.indexOf( event.key ) < 0 || event.key !== this.pattern[ this.current ] ) {
			this.current = 0;
			return;
		}

		// Update how much of the pattern is complete
		this.current++;

		// If complete show theme preview div
		if ( this.pattern.length === this.current ) {
			this.current = 0;
			this.element.classList.remove( 'hidden' );
		}
	}

	addEventListeners() {
		this.buttons.forEach( button => {
			button.addEventListener( 'click', ( event: Event ) => {
				const currentTarget: HTMLElement = <HTMLElement>event.currentTarget;
				const theme = currentTarget.getAttribute( 'data-theme' );
				document.documentElement.removeAttribute( 'class' );

				if ( theme ) {
					document.documentElement.classList.add( theme );
				}
			} );
		} );
	}
}

export default ThemeColorPreview;
