import { ResponsiveHelper } from '@level-level/recyclops';

( function( $, app ) {

	app.primaryNav = function( element ) {

		const mainClass  = 'primary-nav';
		const classNames = {
			parent: `${ mainClass }__item--parent`,
			link:   `${ mainClass }__link`,
			arrow:  `${ mainClass }__arrow`,
			sub:    `${ mainClass }-sub`,
		};

		const $element        = $( element ),
			$parentItem       = $element.find( `.${ classNames.parent }` ),
			$enclosingElement = $element.parents( 'header' ),
			responsiveHelper  = new ResponsiveHelper();

		function initialize() {
			createSubmenuButton();
			addEventListeners();
		}

		/**
		 * Create a dropdown trigger for all parent items
		 */
		function createSubmenuButton() {
			const button =
				`<button class="${ classNames.arrow }" aria-expanded="false">
					&darr;
					<span class="screen-reader-text">${ i18n.navigation.subMenu }</span>
				</button>`;

			// Add button before sub menu
			$parentItem.find( `.${ classNames.sub }` ).before( button );

			// Add aria attribute to all sub-menu's
			$element.find( `.${ classNames.sub }` ).attr( 'aria-hidden', true );
		}

		function addEventListeners() {
			$( window ).on( 'load resize orientationchange', determineViewport );

			// On click on the new dropdown button
			$element.find( `.${ classNames.arrow }` ).on( 'click', showMobileDropdown );

			$element.on( 'keydown', handleKeyboard );
			$enclosingElement.on( 'keyup', closeAllWhenNotActive );
		}

		function determineViewport() {

			// Dropdowns for desktops are using the mouseenter/mouseleave functionality
			if ( responsiveHelper.isDesktop() ) {
				$parentItem.on( 'mouseenter', showDropdown );
				$parentItem.on( 'mouseleave', hideDropdown );
			}

			// Only on viewports that are atleast the tablet landscape size close dropdowns that are not active
			if ( responsiveHelper.atLeastTabletLandscape() ) {
				$element.on( 'keyup', handleTab );
			} else {
				$element.off( 'keyup' );
			}
		}

		/**
		 * Show dropdown on mouseenter, set aria logic
		 */
		function showDropdown() {
			$( this ).children( `.${ classNames.sub }` ).attr( 'aria-hidden', false );
			$( this ).children( `.${ classNames.arrow }` ).attr( 'aria-expanded', true );
		}

		/**
		 * Hide dropdown on mouseleave, set aria logic
		 */
		function hideDropdown() {
			$( this ).children( `.${ classNames.sub }` ).attr( 'aria-hidden', true );
			$( this ).children( `.${ classNames.arrow }` ).attr( 'aria-expanded', false );
		}

		/**
		 * Close all open dropdowns
		 */
		function closeAll() {
			$element.find( `.${ classNames.sub }` ).attr( 'aria-hidden', true );
			$element.find( `.${ classNames.arrow }` ).attr( 'aria-expanded', false );
		}

		/**
		 * Show and hide dropdowns on click for mobile and tablet portrait
		 */
		function showMobileDropdown() {
			const $dropdownButton = $( this );

			// Show dropdown
			if ( $dropdownButton.attr( 'aria-expanded' ) === 'false' ) {
				$dropdownButton.attr( 'aria-expanded', true );
				$dropdownButton.next().attr( 'aria-hidden', false );

			// Hide dropdown
			} else {
				$dropdownButton.attr( 'aria-expanded', false );
				$dropdownButton.next().attr( 'aria-hidden', true );
			}
		}

		/**
		 * Close dropdowns with the escape key
		 *
		 * @param {*} event
		 */
		function handleKeyboard( event ) {

			// Close dropdowns with the escape key
			if ( event.which === 27 ) {
				closeAll();
			}
		}

		/**
		 * Closes dropdowns when tabbing through the navigation
		 *
		 * @param {*} event
		 */
		function handleTab( event ) {
			if ( event.which === 9 ) {

				// Current element that is focused
				const $focusedElement = $element.find( ':focus' );

				// When tabbing through top level links, close all dropdowns
				if ( $focusedElement.attr( 'class' ) === classNames.link ) {
					$element.find( `.${ classNames.sub }` ).attr( 'aria-hidden', true );
					$element.find( `.${ classNames.arrow }` ).attr( 'aria-expanded', false );

				// When tabbing through sub menu links, close all sub dropdowns
				} else if ( $focusedElement.closest( `.${ classNames.sub }` ).length ) {
					$focusedElement.closest( `.${ classNames.sub }` ).find( `.${ classNames.sub }` ).attr( 'aria-hidden', true );
					$focusedElement.closest( `.${ classNames.sub }` ).find( `.${ classNames.arrow }` ).attr( 'aria-expanded', false );
				}
			}
		}

		/**
		 * Closes dropdowns when tabbing out of the navigation
		 *
		 * @param {*} event
		 */
		function closeAllWhenNotActive( event ) {
			if ( event.which === 9 && !event.shiftKey ) {

				// Current element that is focused
				const $focusedElement = $element.find( ':focus' );

				if ( $focusedElement.length === 0 ) {
					closeAll();
				}

			}
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
