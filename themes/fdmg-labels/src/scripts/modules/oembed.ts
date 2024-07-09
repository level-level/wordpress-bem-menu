import { IModule } from '../../globals';
import playerjs from 'player.js';

type EmbedType = 'video' | 'audio';

declare global {
	interface Window {
		__tcfapi: any;
	}
	interface Window {
		_sp_: {
			gdpr: {
				deleteConsentCookies: () => void;
				loadPrivacyManagerModal: (e: number, n: string) => void;
			};
		};
	}
}

class OEmbed implements IModule {
	element: HTMLElement;
	button: HTMLElement | null;
	dataAttribute: string;
	playBtnClass: string;
	source: string | undefined;
	type: EmbedType;
	disableNotice: string | undefined;
	cmpId: number | undefined;
	theme: string | undefined;


	constructor(element: HTMLElement) {
		this.element = element;
		this.dataAttribute = 'data-oembed';
		this.playBtnClass = `btn--js-video-play`;
		this.button = this.element.querySelector(`.js-consent-button`);
		this.source = this.element.dataset.source;
		this.type = this.element.dataset.type as EmbedType;
		this.theme = this.element.dataset.theme;
		this.disableNotice = this.element.dataset.disableNotice;
		this.cmpId = this.element.dataset.cmpId ? parseInt(this.element.dataset.cmpId) : undefined;
		this.initialize();
	}

	initialize() {
		if (this.disableNotice === '1') {
			this.showEmbed();
			return;
		}

		if (typeof window.__tcfapi !== 'undefined') {
			this.initSourcepoint();
		} else {
			this.initDefault();
		}
	}

	/**
	 * Use Sourcepoint to handle user consent
	 */
	initSourcepoint() {
		this.button?.addEventListener('click', () => {
			if (this.cmpId) {
				window._sp_.gdpr.loadPrivacyManagerModal(this.cmpId, 'purposes');
			}
		});

		// Get consent data if the tcfapi is loaded or when a user has changed something in their consent settings
		window.__tcfapi('addEventListener', 2, (tcData: { eventStatus: string }) => {
			if (tcData.eventStatus === 'tcloaded' || tcData.eventStatus === 'useractioncomplete') {
				// Get the consent data
				window.__tcfapi(
					'getCustomVendorConsents',
					2,
					(data: {
						consentedPurposes: {
							_id: string;
							name: string;
						}[];
						consentedVendors: {
							_id: string;
							name: string;
						}[];
					}) => {
						const embedSource = this.source;

						// Check if we have consent for the specific purpose of showing embeds in content
						let purposeConsent = false;
						data.consentedPurposes.forEach((element) => {
							if (element.name === 'Verrijken van artikelen' || element._id === '62a1c6502b246104cd6ebda5') {
								purposeConsent = true;
							}
						});

						// Check if we have consent for the vendor of the current embed
						let vendorConsent = false;
						data.consentedVendors.forEach((element) => {
							if (embedSource?.includes(element.name.toLowerCase())) {
								vendorConsent = true;
							}
						});

						// Show the embed if we have both purpose and vendor consent
						if ((purposeConsent && vendorConsent) || (this.source === 'consent_embed_omny' && purposeConsent)) {
							this.showEmbed();
						}

						// If the user has withdrawn his/her consent, we reload the page
						else if (tcData.eventStatus === 'useractioncomplete') {
							window.location.reload();
						}
					},
				);
			}
		});
	}

	initDefault() {
		this.button?.addEventListener('click', () => {
			if (this.source) {
				window.localStorage.setItem(this.source, 'true');
			}
			this.showEmbed();

			// Show all available embeds with the same source
			const sourceElements = Array.from(document.querySelectorAll(`[data-source="${this.source}"]`)) as HTMLElement[];
			sourceElements.forEach((element) => {
				this.showEmbed(element);
			});
		});

		if (this.source && window.localStorage.getItem(this.source)) {
			this.showEmbed();
		}
	}

	showEmbed(embed?: HTMLElement) {
		if (embed === undefined) {
			embed = this.element;
		}

		const content = embed.querySelector('template');

		if (!content) {
			return;
		}
		const OEmbedContent = embed.querySelector(`[${this.dataAttribute}="content"]`);

		if (!OEmbedContent) {
			return;
		}

		/**
		 * Load the Twitter embed script if the embed is a Twitter embed
		 * This is needed because WordPress doesn't allow script tags in the content
		 * More info: https://developer.twitter.com/en/docs/twitter-for-websites/embedded-tweets/faq
		 */
		if (this.source === 'consent_embed_twitter') {
			const widgetUrl = '//platform.twitter.com/widgets.js';
			const existingScript = document.querySelector(`script[src="${widgetUrl}"]`);

			if (!existingScript) {
				const script = document.createElement('script');
				script.src = widgetUrl;
				script.async = true;
				document.body.appendChild(script);
			}
		}

		OEmbedContent.innerHTML = content.innerHTML;

		if (this.type === 'video') {
			this.lazyLoadVideo(embed);
		} else if (this.type === 'audio') {
			this.lazyLoadAudio(embed);
		}
	}

	lazyLoadVideo(embed: HTMLElement) {
		const videoElement = embed.querySelector(`[${this.dataAttribute}="video"]`) as HTMLElement | null;

		if (!videoElement) {
			return;
		}

		videoElement.addEventListener('click', () => {
			this.playVideo(videoElement);
		});
	}

	lazyLoadAudio(embed: HTMLElement) {
		const audioElement = embed.querySelector(`[${this.dataAttribute}="audio"]`) as HTMLElement | null;

		if (!audioElement) {
			return;
		}

		const title = audioElement?.querySelector('iframe')?.getAttribute('title');

		if (title && title?.length > 0) {
			const titleElement = audioElement.querySelector(`[${this.dataAttribute}="audio-title"]`);

			if (!titleElement) {
				return;
			}

			titleElement.textContent = title;
		}

		audioElement.addEventListener('click', () => {
			this.playAudio(audioElement);
		});
	}

	playVideo(videoElement: HTMLElement) {
		const iframe = videoElement.querySelector('iframe');
		const src = iframe?.getAttribute('data-src');

		iframe?.setAttribute('src', src + '&autoplay=1&disablekb=1&keyboard=0');

		const playButton = videoElement.querySelector(`.${this.playBtnClass}`);
		const thumbnail = videoElement.querySelector(`[${this.dataAttribute}="thumbnail"]`);

		playButton?.remove();
		thumbnail?.remove();
	}

	playAudio(embedElement: HTMLElement) {
		const iframe = embedElement.querySelector('iframe');
		const src = iframe?.getAttribute('data-src');

		if (!src || !iframe) {
			return;
		}

		iframe.allow = 'autoplay';
		iframe.setAttribute('src', src + '&auto_play=true&hide_related=true&show_comments=false&show_reposts=false');

		const placeholder = embedElement.querySelector(`[${this.dataAttribute}="placeholder"]`);
		placeholder?.remove();

		if (this.source === 'consent_embed_omny') {

			if (this.theme === 'energeia') {
				iframe.setAttribute('src', src + '&utm_source=CustomPlayer8');
			}

			if (this.theme === 'pensioen-pro') {
				iframe.setAttribute('src', src + '&utm_source=CustomPlayer9');
			}

			// Removing the sandbox attribute will allow us to use the player.js library
			iframe.removeAttribute('sandbox');
			this.initOmnyPlayer(iframe);
		}
	}

	initOmnyPlayer(iframe: HTMLIFrameElement) {
		const player = new playerjs.Player(iframe);

		player.on('ready', () => {
			player.play();
		});
	}
}

export default OEmbed;
