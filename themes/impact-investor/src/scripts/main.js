import './modules/primary-nav.js';
import './modules/toggle.js';
import './modules/header-search.js';
import './modules/skiplink.js';
import './modules/example-module.js';
import './modules/cookiebot.js';
import './modules/oembed.js';
import './modules/table.js';
import './modules/social-share.js';
import './modules/tabs.js';

( function( app, $ ) {
	'use strict';

	app.instantiate = function( elem ) {
		const $this   = $( elem );
		const module  = $this.attr( 'data-module' );
		if ( module === undefined ) {
			throw 'Module not defined (use data-module="")';
		} else if ( module in app ) {
			new app[ module ]( elem );
			$this.attr( 'data-initialized', true );
		} else {
			throw 'Module \'' + module + '\' not found';
		}
	};

	$( '[data-module]' ).each( function() {
		app.instantiate( this );
	} );

	function handleFirstTab( e ) {
		if ( 9 === e.keyCode ) {
			$( 'body' ).addClass( 'user-is-tabbing' );
			document.defaultView.removeEventListener( 'keydown', handleFirstTab );
		}
	}

	document.defaultView.addEventListener( 'keydown', handleFirstTab );

	/* eslint-disable no-console */
	console.log( '%c  Powered by level-level.com 💪🏻  ', 'background: #222; color: #fff; font-size: 14px; font-weight: normal; border-radius: 5px; line-height: 30px;' );
	/* eslint-enable no-console */
}( window.app = window.app || {}, jQuery ) );
