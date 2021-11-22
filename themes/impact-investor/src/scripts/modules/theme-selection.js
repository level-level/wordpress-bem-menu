( function( $, app ) {

	app.themeSelection = function( element ) {

		const $element = $( element ),
			$radio = $element.find( 'input[type="radio"]' ),
			$html = $( 'html' );

		const currentTheme = localStorage.getItem( 'theme-style' );

		// Initialize an instance
		function initialize() {
			initialLoad();
			addEventListeners();
		}

		// On initial load check if there is a theme available in the localStorage
		function initialLoad() {
			if ( currentTheme ) {
				setActiveTheme( currentTheme );
			}
		}

		// Set active theme based on radiobutton selection
		function addEventListeners() {
			$radio.on( 'change', function() {
				const selectedTheme = $( this ).val();
				setActiveTheme( selectedTheme );
			} );
		}

		function setActiveTheme( theme ) {

			// Reset all theme classes
			$html.removeClass( 'theme-light' );
			$html.removeClass( 'theme-dark' );
			$html.removeClass( 'theme-system' );

			// Uncheck any checked radiobuttons
			$radio.prop( 'checked', false );

			// Add body classes based on theme
			if ( theme === 'light' ) {
				$html.addClass( 'theme-light' );
			} else if ( theme === 'dark' ) {
				$html.addClass( 'theme-dark' );
			} else if ( theme === 'system' ) {
				$html.addClass( 'theme-system' );
			}

			// Check radiobutton with active theme
			$( `:radio[value="${ theme }"]` ).prop( 'checked', true );

			// Save theme preference in the localStorage
			localStorage.setItem( 'theme-style', theme );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
