( function( $, app ) {

	app.socials = function( element ) {
		const $element = $( element ),
			$shareLink = $element.find( '.js--share' );

		function initialize() {
			addEventListeners();
		}

		// Add event listeners
		function addEventListeners() {
			$shareLink.on( 'click', handleClick );
		}

		const platforms = {
			twitter:    'http://twitter.com/share?url=',
			facebook:   'https://www.facebook.com/sharer/sharer.php?u=',
			googleplus: 'https://plus.google.com/share?url=',
			linkedin:   'https://www.linkedin.com/shareArticle?mini=true&url=',
			whatsapp:   'https://api.whatsapp.com/send?text=',
		};

		// Handle click on social item
		function handleClick( e ) {
			const config = $( this ).data( 'config' ) || {};
			const type = config.type;
			const text = config.shareText || i18n.socialShare;
			const body = `${ config.shareUrl }`;
			const emailSubject = `'${ config.shareTitle }' | ${ i18n.emailSubject }`;
			const emailBody = `${ i18n.socialShare } ${ config.shareUrl }`;

			const location = {
				twitter:    `${ platforms.twitter }${ body }`,
				facebook:   `${ platforms.facebook }${ body }`,
				googleplus: `${ platforms.googleplus }${ body }`,
				linkedin:   `${ platforms.linkedin }${ body }`,
				whatsapp:   `${ platforms.whatsapp }${ text }: ${ window.location.href }`,
			};

			if ( type === 'email' ) {
				$( this ).attr( 'href', `mailto:?subject=${ emailSubject }&body=${ emailBody }` );
			} else if ( typeof location[ type ] !== 'undefined' ) {

				const width = 550;
				const height = 450;
				const top = ( window.screen.height/2 ) - ( height/2 );

				window.open(
					location[ type ],
					location[ type ].charAt( 0 ).toUpperCase() + location[ type ].slice( 1 ),
					`height=${ height }, width=${ width } top=${ top } toolbar=0, location=0, menubar=0, directories=0, scrollbars=0`
				);
				e.preventDefault();
			}
		}

		initialize();
	};

}( jQuery, window.app = window.app || {} ) );
