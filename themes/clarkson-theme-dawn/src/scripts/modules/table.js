( function( $, app ) {

	app.table = function( element ) {

		const $element = $( element ),
			$tableHeader = $element.find( 'th' ),
			$tableRows = $element.find( 'tbody tr, tfoot tr' );

		// Initialize an instance
		function initialize() {
			addTableHeaderProp();
		}

		function addTableHeaderProp() {

			const headerItems = [];

			for ( const value of $tableHeader ) {
				headerItems.push( $( value ).text() );
			}

			for ( const value of $tableRows ) {
				const td = $( value ).find( 'td' );

				for ( let i = 0; i < td.length; i++ ) {
					const ele = $( td[ i ] );
					ele.attr( 'data-th', headerItems[ i ] );
				}
			}
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
