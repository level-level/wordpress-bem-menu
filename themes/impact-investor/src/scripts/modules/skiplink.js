( function( $, app ) {
	app.skiplink = function( element ) {

		const $element      = $( element );

		// Initialize an instance
		function initialize() {
			addEventListeners();
		}

		function addEventListeners() {
			$element.on( 'click', handleClick );
		}

		function handleClick() {
			const target = $( this ).attr( 'href' );
			$( 'html, body' ).animate( {
				scrollTop: $( target ).offset().top,
			}, 200 );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
