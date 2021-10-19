import { MrToggle } from '@level-level/recyclops';

( function( $, app ) {

	app.headerSearch = function( element ) {

		const $element = $( element ),
			$backdrop = $element.find( '.header-search__backdrop' );

		function initialize() {
			const options = {
				moduleName: 'headerSearch',
				useHover: false,
			};

			const toggle = new MrToggle( $element, options );

			$element.on( 'openMrToggle', () => {
				$element.find( '.js-header-search-open' ).prop( 'disabled', true );
			} );

			$element.on( 'closeMrToggle', () => {
				$element.find( '.js-header-search-open' ).prop( 'disabled', false );
			} );

			$backdrop.on( 'click', () => {
				toggle.closeAllToggles();
			} );

			$element.on( 'openMrToggle', () => {
				showBackdrop();
			} );

			$element.on( 'closeMrToggle', () => {
				if ( $element.find( '.header-search__dropdown' ).attr( 'aria-hidden' ) === 'true' ) {
					hideBackdrop();
				}
			} );
		}

		function showBackdrop() {
			$backdrop.addClass( 'header-search__backdrop--active' );
		}

		function hideBackdrop() {
			$backdrop.removeClass( 'header-search__backdrop--active' );
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
