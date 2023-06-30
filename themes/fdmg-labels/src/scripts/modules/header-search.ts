import { MrToggle } from '@level-level/recyclops';
import { IOptions } from '@level-level/recyclops/types/mr-toggle';
import { IModule } from '../../globals';

class HeaderSearch implements IModule {
	element: HTMLElement;
	backdrop: HTMLElement | null;
	headerOpen: HTMLInputElement | null;
	dropdown: HTMLElement | null;

	constructor ( element: HTMLElement ) {
		this.element = element;
		this.backdrop = this.element.querySelector( '.header-search__backdrop' );
		this.headerOpen = this.element.querySelector( '.js-header-search-open' );
		this.dropdown = this.element.querySelector( '.header-search__dropdown' );

		this.initialize();
	}

	initialize() {
		const options = {
			useHover: false,
		};

		const toggle = new MrToggle( this.element, <IOptions>options );

		this.element.addEventListener( 'openMrToggle', () => {
			setTimeout( () => {
				if ( this.headerOpen ) {
					this.headerOpen.disabled = true;
				}
			}, 300 );
		} );

		this.element.addEventListener( 'closeMrToggle', () => {
			if ( this.headerOpen ) {
				this.headerOpen.disabled = false;
			}
		} );

		this.backdrop?.addEventListener( 'click', () => {
			toggle.closeAllToggles();
		} );

		this.element.addEventListener( 'openMrToggle', () => {
			this.showBackdrop();
		} );

		this.element.addEventListener( 'closeMrToggle', () => {
			if ( this.dropdown?.getAttribute( 'aria-hidden' ) === 'true' ) {
				this.hideBackdrop();
			}
		} );
	}

	showBackdrop() {
		this.backdrop?.classList.add( 'header-search__backdrop--active' );
	}

	hideBackdrop() {
		this.backdrop?.classList.remove( 'header-search__backdrop--active' );
	}
}

export default HeaderSearch;
