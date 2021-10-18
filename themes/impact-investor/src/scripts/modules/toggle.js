import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.toggle = function( element ) {

		const $element = $( element );

		function initialize() {
			const options = {
				moduleName: 'toggle',
				useHover: false,
			};

			new MrToggle( $element, options );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
