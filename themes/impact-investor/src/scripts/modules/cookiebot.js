( function( $, app ) {

	app.cookiebot = function( element ) {

		const $element      = $( element ),
			config          = $element.data( 'config' );

		// Initialize an instance
		function initialize() {
			if ( typeof Cookiebot === 'undefined' ) {
				return;
			}
			addEventListeners();
		}

		function addEventListeners() {
			$element.on( 'click', buttonClick );
		}

		function buttonClick( e ) {
			e.preventDefault();

			let action = 'renew';
			if ( config && config.action ) {
				action = config.action;
			}

			switch ( action ) {
			case 'renew':
				Cookiebot.renew();
				break;
			case 'withdraw':
				Cookiebot.withdraw();
				break;
			}
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
