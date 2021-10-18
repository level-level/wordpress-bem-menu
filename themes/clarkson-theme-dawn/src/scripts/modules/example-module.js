/**
 * This is an example module, use it as a template but remove the import from the 'main.js'
 */
( function( $, app ) {

	app.hello = function( element ) {

		const $element      = $( element ),
			config          = $element.data( 'config' ),
			$randomGreeting = $element.find( '.random-greeting' );

		let random;

		// Initialize an instance
		function initialize() {
			addEventListeners();
		}

		function addEventListeners() {
			$element.on( 'click', ( e ) => {
				if ( $( e.currentTarget ).is( ':button' ) ) {
					$element.find( '.btn__text' ).text( config.clicked );
				}
			} );

			setInterval( () => {
				if ( config && config.greetings ) {
					random = config.greetings[ Math.floor( Math.random() * config.greetings.length ) ];
					$randomGreeting.html( random );
				}
			}, 500 );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
