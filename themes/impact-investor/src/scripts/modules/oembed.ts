import { IModule } from '../../globals';

declare global {
	interface Window { __tcfapi: any; }
	interface Window { _sp_: {
		gdpr: {
			deleteConsentCookies: () => void;
			loadPrivacyManagerModal: ( e: number, n: string ) => void;
		};
	}; }
}

class OEmbed implements IModule {
	element: HTMLElement;
	button: HTMLElement;
	source: string;
	type: string | undefined;
	disableNotice: string | undefined;

	constructor( element: HTMLElement ) {
		this.element = element;
		this.button = <HTMLElement>this.element.querySelector( '.js-consent-button' );
		this.source = <string>this.element.dataset.source;
		this.type = <string>this.element.dataset.type;
		this.disableNotice = this.element.dataset[ 'disable-notice' ];
		this.initialize();
	}

	initialize() {
		if ( this.disableNotice === '1' ) {
			this.showEmbed();
			return;
		}

		if ( typeof window.__tcfapi !== 'undefined' ) {
			this.initSourcepoint();
		} else {
			this.initDefault();
		}
	}

	/**
		* Use Sourcepoint to handle user consent
		*/
	initSourcepoint() {

		this.button.addEventListener( 'click', () => {
			window._sp_.gdpr.loadPrivacyManagerModal( 656244, 'purposes' );
		} );

		// Get consent data if the tcfapi is loaded or when a user has changed something in their consent settings
		window.__tcfapi( 'addEventListener', 2, ( tcData: { eventStatus: string } ) => {
			if ( tcData.eventStatus === 'tcloaded' || tcData.eventStatus === 'useractioncomplete' ) {

				// Get the consent data
				window.__tcfapi( 'getCustomVendorConsents', 2, ( data: {
					consentedPurposes: {
						_id: string;
						name: string;
					}[];
					consentedVendors: {
						_id: string;
						name: string;
					}[]
				} ) => {
					const embedSource = this.source;

					// Check if we have consent for the specific purpose of showing embeds in content
					let purposeConsent = false;
					data.consentedPurposes.forEach( ( element ) => {
						if ( element.name === 'Verrijken van artikelen' || element._id === '62a1c6502b246104cd6ebda5' ) {
							purposeConsent = true;
						}
					} );

					// Check if we have consent for the vendor of the current embed
					let vendorConsent = false;
					data.consentedVendors.forEach( ( element ) => {
						if ( embedSource.includes( element.name.toLowerCase() ) ) {
							vendorConsent = true;
						}
					} );

					// Show the embed if we have both purpose and vendor consent
					if ( purposeConsent && vendorConsent ) {
						this.showEmbed();
					}

					// If the user has withdrawn his/her consent, we reload the page
					else if ( tcData.eventStatus === 'useractioncomplete' ) {
						window.location.reload();
					}
				} );
			}
		} );
	}

	initDefault() {
		this.button.addEventListener( 'click', () => {
			window.localStorage.setItem( this.source, 'true' );
			this.showEmbed();

			// Show all available embeds with the same source
			document.querySelectorAll<HTMLElement>( `[data-source="${ this.source }"]` ).forEach( ( el: HTMLElement ) => {
				this.showEmbed( el );
			} );

		} );

		if ( window.localStorage.getItem( this.source ) ) {
			this.showEmbed();
		}
	}

	showEmbed( embed?: HTMLElement ) {
		if ( embed === undefined ) {
			embed = this.element;
		}

		const content = embed.querySelector( 'template' );

		if ( ! content ) {
			return;
		}
		const OEmbedContent = embed.querySelector( '.oembed-component__content' );

		if ( ! OEmbedContent ) {
			return;
		}

		OEmbedContent.innerHTML = content.innerHTML;

		if ( this.type === 'video' ) {
			this.lazyLoadVideo( embed );
		} else if ( this.type === 'audio' ) {
			this.lazyLoadAudio( embed );
		}
	}

	lazyLoadVideo( embed: HTMLElement ) {
		const videoElement: HTMLElement | null = embed.querySelector( '.oembed-video' );

		if ( ! videoElement ) {
			return;
		}

		videoElement.addEventListener( 'click', () => {
			this.playVideo( videoElement );
		} );
	}

	lazyLoadAudio( embed: HTMLElement ) {
		const audioElement: HTMLElement | null = embed.querySelector( '.oembed-audio' );

		if ( ! audioElement ) {
			return;
		}

		const title = audioElement?.querySelector( 'iframe' )?.getAttribute( 'title' );

		if ( title && title?.length > 0 ) {
			const titleElement = audioElement.querySelector( '.oembed-audio--js-title' );

			if ( ! titleElement ) {
				return;
			}

			titleElement.textContent = title;
		}

		audioElement.addEventListener( 'click', () => {
			this.playAudio( audioElement );
		} );
	}

	playVideo( videoElement: HTMLElement ) {
		const iframe = videoElement.querySelector( 'iframe' );
		const src = iframe?.getAttribute( 'data-src' );

		iframe?.setAttribute( 'src', src + '&autoplay=1&disablekb=1&keyboard=0' );

		const playButton = videoElement.querySelector( '.btn--js-video-play' );
		const thumbnail = videoElement.querySelector( '.oembed-video__thumbnail' );

		playButton?.remove();
		thumbnail?.remove();
	}

	playAudio( embedElement: HTMLElement ) {
		const iframe = embedElement.querySelector( 'iframe' );
		const src = iframe?.getAttribute( 'data-src' );

		if ( ! src ) {
			return;
		}

		iframe?.setAttribute( 'src', src );

		const playButton = embedElement.querySelector( '.btn--js-audio-play' );
		const title = embedElement.querySelector( '.oembed-audio--js-title' );

		playButton?.remove();
		title?.remove();
	}
}

export default OEmbed;
