import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.socialShareDropdown = function( element ) {

		const $element = $( element );

		function initialize() {
			const options = {
				moduleName: 'socialShareDropdown',
				useHover: false,
			};

			const toggle = new MrToggle( $element, options );

			// Close dropdown when clicking outside of it
			$( document ).on( 'click', ( e ) => {
				if ( ! $element.is( e.target ) && $element.has( e.target ).length === 0 ) {
					toggle.closeAllToggles();
				}
			} );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
