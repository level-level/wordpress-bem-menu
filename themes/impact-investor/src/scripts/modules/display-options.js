import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.displayOptions = function( element ) {

		const $element = $( element );

		function initialize() {
			const options = {
				moduleName: 'displayOptions',
				useHover: false,
			};

			new MrToggle( $element, options );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
