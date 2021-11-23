import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.headerProfile = function( element ) {

		const $element = $( element ),
			$backdrop = $element.find( '.header-profile__backdrop' );

		function initialize() {
			const options = {
				moduleName: 'headerProfile',
				useHover: false,
			};

			const toggle = new MrToggle( $element, options );

			$backdrop.on( 'click', () => {
				toggle.closeAllToggles();
			} );

			$element.on( 'openMrToggle', () => {
				showBackdrop();
			} );

			$element.on( 'closeMrToggle', () => {
				if ( $element.find( '.header-profile__dropdown' ).attr( 'aria-hidden' ) === 'true' ) {
					hideBackdrop();
				}
			} );
		}

		function showBackdrop() {
			$backdrop.addClass( 'header-profile__backdrop--active' );
		}

		function hideBackdrop() {
			$backdrop.removeClass( 'header-profile__backdrop--active' );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
