import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.headerMenu = function( element ) {

		const $element = $( element ),
			$backdrop = $element.find( '.header-menu__backdrop' );

		function initialize() {
			const options = {
				moduleName: 'headerMenu',
				useHover: false,
			};

			const toggle = new MrToggle( $element, options );

			// Set initial menu button to disable when menu is open
			$element.on( 'openMrToggle', () => {
				$element.find( '.js-header-menu-open' ).prop( 'disabled', true );
			} );

			// Set initial menu button to enable when menu is close
			$element.on( 'closeMrToggle', () => {
				$element.find( '.js-header-menu-open' ).prop( 'disabled', false );
			} );

			$backdrop.on( 'click', () => {
				toggle.closeAllToggles();
			} );

			$element.on( 'openMrToggle', () => {
				showBackdrop();
			} );

			$element.on( 'closeMrToggle', () => {
				if ( $element.find( '.header-menu__dropdown' ).attr( 'aria-hidden' ) === 'true' ) {
					hideBackdrop();
				}
			} );
		}

		function showBackdrop() {
			$backdrop.addClass( 'header-menu__backdrop--active' );
		}

		function hideBackdrop() {
			$backdrop.removeClass( 'header-menu__backdrop--active' );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
