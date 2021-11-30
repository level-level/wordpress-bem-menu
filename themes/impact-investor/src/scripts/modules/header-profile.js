import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.headerProfile = function( element ) {

		const $element = $( element );

		function initialize() {
			const options = {
				useHover: false,
			};

			const toggle = new MrToggle( $element, options );

			// Close toggle when clicking outside of element
			$( document ).on( 'click', ( e ) => {
				if ( ! $element.is( e.target ) && $element.has( e.target ).length === 0 ) {
					toggle.closeAllToggles();
				}
			} );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
