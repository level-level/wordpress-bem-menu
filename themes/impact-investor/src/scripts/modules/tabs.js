( function( $, app ) {

	app.tabs = function( element ) {

		const $element  = $( element ),
			$tabLink    = $element.find( '.tab-list__link' ),
			$tabSection = $element.find( '.tab-list__section' );

		function initialize() {
			addEventListeners();
		}

		function addEventListeners() {

			// Handle clicking of tabs for mouse users
			$tabLink.on( 'click', function( e ) {
				e.preventDefault();

				if ( ! $( this ).attr( 'aria-selected' ) ) {
					const currentTab = $element.find( '[aria-selected]' );
					if ( e.currentTarget !== currentTab ) {
						switchTab( currentTab, $( e.currentTarget ) );
					}
				}
			} );

			$tabLink.on( 'keydown', function( e ) {
				const $current = $( e.currentTarget );

				// Arrow key: left
				if ( 37 === e.which ) {
					const $previousTab = $current.parent().prev();

					if ( $previousTab.hasClass( 'tab-list__item' ) ) {
						$current.blur();
						$previousTab.find( '.tab-list__link' ).focus();
						switchTab( $current, $previousTab.find( '.tab-list__link' ) );
					}
				}

				// Arrow key: right
				if ( 39 === e.which ) {
					const $nextTab = $current.parent().next();
					if ( $nextTab.hasClass( 'tab-list__item' ) ) {
						$current.blur();
						$nextTab.find( '.tab-list__link' ).focus();
						switchTab( $current, $nextTab.find( '.tab-list__link' ) );
					}
				}
			} );
		}

		// Switch tab logic, applies logic to the old tab and the new
		function switchTab( oldTab, newTab ) {
			newTab.focus();
			newTab.attr( 'aria-selected', true );
			newTab.attr( 'tabindex', 0 );
			oldTab.removeAttr( 'aria-selected' );
			oldTab.attr( 'tabindex', -1 );
			$tabSection.attr( 'aria-hidden', true );
			const current = newTab.attr( 'href' );

			// Loop over the tab sections and find the one with the same id as the tab link
			$tabSection.each( function( i, el ) {
				if ( `#${$( el ).attr( 'id' )}` === current ) {
					$( this ).attr( 'aria-hidden', false );
				}
			} );
		}

		initialize();

	};
}( jQuery, window.app = window.app || {} ) );
