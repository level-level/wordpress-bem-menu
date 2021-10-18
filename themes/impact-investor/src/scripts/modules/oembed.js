
( function( $, app ) {

	app.oembed = function( element ) {

		const $element = $( element ),
			$button = $element.find( '.js-consent-button' ),
			isCookiebot = $element.data( 'cookiebot' ),
			source = $element.data( 'source' ),
			type = $element.data( 'type' ),
			disableNotice = $element.data( 'disable-notice' );

		// Initialize an instance
		function initialize() {
			if ( disableNotice === 1 ) {
				showEmbed();
				return;
			}

			if ( isCookiebot && typeof Cookiebot !== 'undefined' ) {
				initCookiebot();
			} else {
				initDefault();
			}
		}

		function initCookiebot() {
			if ( Cookiebot.consent.marketing ) {
				showEmbed();
			} else {
				$( window ).on( 'CookiebotOnAccept', () => {
					showEmbed();
				} );

				$button.on( 'click', () => {
					Cookiebot.show();
				} );
			}
		}

		function initDefault() {
			$button.on( 'click', () => {
				window.localStorage.setItem( source, true );
				showEmbed();

				// Show all available embeds with the same source
				$( `[data-source="${source}"]` ).each( function() {
					showEmbed( $( this ) );
				} );

			} );

			if ( window.localStorage.getItem( source ) ) {
				showEmbed();
			}
		}

		function showEmbed( $embed ) {
			if( $embed === undefined ) {
				$embed = $element;
			}

			const $content = $embed.find( 'template' );
			$embed.find( '.oembed-component__content' ).html( $content.html() );

			if ( type === 'video' ) {
				lazyLoadVideo( $embed );
			} else if ( type === 'audio' ) {
				lazyLoadAudio( $embed );
			}
		}

		function lazyLoadVideo( $embed ) {
			const $videoElement = $embed.find( '.oembed-video' );

			$videoElement.on( 'click', () => {
				playVideo( $videoElement );
			} );
		}

		function lazyLoadAudio( $embed ) {
			const $embedElement = $embed.find( '.oembed-audio' );
			const title = $embedElement.find( 'iframe' ).attr( 'title' );

			if ( typeof title !== 'undefined' && title.length > 0 ) {
				const $titleElement = $embedElement.find( '.oembed-audio--js-title' );
				$titleElement.text( title );
			}

			$embedElement.on( 'click', () => {
				playAudio( $embedElement );
			} );
		}

		function playVideo( $videoElement ) {
			const $iframe = $videoElement.find( 'iframe' );
			const src = $iframe.attr( 'data-src' );

			$iframe.attr( 'src', src + '&autoplay=1&disablekb=1&keyboard=0' );
			$videoElement.find( '.btn--js-video-play' ).remove();
			$videoElement.find( '.oembed-video__thumbnail' ).remove();
		}

		function playAudio( $embedElement ) {
			const $iframe = $embedElement.find( 'iframe' );
			const src = $iframe.attr( 'data-src' );

			$iframe.attr( 'src', src );
			$embedElement.find( '.btn--js-audio-play' ).remove();
			$embedElement.find( '.oembed-audio--js-title' ).remove();
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
