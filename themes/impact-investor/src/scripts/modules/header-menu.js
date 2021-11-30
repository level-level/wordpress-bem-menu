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

			$backdrop.on( 'click', () => {
				toggle.closeAllToggles();
			} );

			$element.on( 'openMrToggle', () => {
				showBackdrop();

				// Set the html element to height 100vh to fix a bug on iOS Safari 15
				// https://stackoverflow.com/questions/69355787/mobile-safari-15-bottom-navigation-area-issue
				$( 'html' ).css( {
					'height': '100vh'
				} );
			} );

			$element.on( 'closeMrToggle', () => {
				if ( $element.find( '.header-menu__dropdown' ).attr( 'aria-hidden' ) === 'true' ) {
					hideBackdrop();
				}

				// Remove style property that fixes iOS Safari 15 issue
				$( 'html' ).removeAttr( 'style' );
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
