import { ResponsiveHelper } from '@level-level/recyclops';

( function( $, app ) {

	app.displayOptions = function( element ) {

		const $element = $( element ),
			responsiveHelper = new ResponsiveHelper();

		function initialize() {
			$element.on( 'keydown', handleKeyboard );
		}

		function handleKeyboard( event ) {
			if ( event.which === 9 && responsiveHelper.atLeastTabletPortrait() ) {
				event.preventDefault();
				$( '.header-profile' ).find( '.btn' ).focus();
			}
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
