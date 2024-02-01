import { IModule } from '../../globals';

window.__tcfapi = window.__tcfapi || undefined;

declare global {
	interface Window {
		dataLayer: any[];
	}
	interface Window {
		__tcfapi: any;
	}
}

class DataLayer implements IModule {
	element: HTMLElement;
	config: {
		id: string;
		title: string;
		url: string;
		author: string;
		type: string;
		publication_date: string;
		publication_time: string;
		revisions_count: string;
		article_index: string;
		component_subtype: string;
		parent_component: string;
		categories: string;
		share_type: string;
	};
	event_type: string;
	do_not_track: boolean;
	pushedScrollPercentages: Set<number>;

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config
			? JSON.parse(this.element.dataset.config)
			: {};
		this.event_type = this.element.dataset.type ?? '';
		this.do_not_track = false;
		this.pushedScrollPercentages = new Set();
		this.initialize();
	}

	initialize() {
		if (this.event_type === 'article_click') {
			this.element.addEventListener('click', this.addArticleClick.bind(this));
		}
		if (this.event_type === 'article_impression') {
			window.addEventListener( 'scroll', this.trackTeaserInViewport.bind(this) );
		}
		if (this.event_type === 'share_article') {
			this.element.addEventListener('click', this.addShareArticleShareEvent.bind(this));
		}
		if (this.event_type === 'active_time_on_page') {
			this.setupActivityTracker();
		}
		if ( this.event_type === 'article_view' ) {
			if ( typeof window.__tcfapi !== 'undefined' ) {
				this.initSourcepoint ( 'article_view' );
			} else {
				console.log( 'article view');
				this.element.addEventListener('copy', this.addCopyText.bind(this));
				this.element.addEventListener('cut', this.addCopyText.bind(this));
				this.trackScrollPercentage();
				// add read percenbtage tracker
			}
		}
	}

	setupActivityTracker() {
		const inactivityThreshold = 10000; // 10 seconds in milliseconds
		let activeTime = 0; // Active time in milliseconds
		let startTime = 0;
		let activityTimer: number | undefined;

		const setInteracting = () => {
			clearTimeout(activityTimer);

			if (startTime === 0) {
				startTime = Date.now();
			}

			activityTimer = window.setTimeout(() => {
				const timeElapsed = Date.now() - startTime;

				// Ensure we don't add negative time
				if (timeElapsed > inactivityThreshold) {
					activeTime += timeElapsed - inactivityThreshold;
				}

				startTime = 0;
			}, inactivityThreshold);
		};

		window.addEventListener('scroll', setInteracting);
		document.body.addEventListener('mousemove', setInteracting);
		document.body.addEventListener('click', setInteracting);
		document.body.addEventListener('touchstart', setInteracting);
		document.body.addEventListener('keydown', setInteracting);

		window.addEventListener('beforeunload', () => {
			if (activeTime === 0) {
				activeTime = Date.now() - startTime;
			}

			window.dataLayer.push({
				event: 'active_time_on_page',
				event_info: {
					category: 'engagement',
					action: 'active time',
					label: 'Time spent actively on page',
					value: Math.ceil(activeTime / 1000), // Convert milliseconds to seconds
					event_context: {
						article_id: `${this.config.id}`,
						hostname: `${window.location.hostname}`,
						pathname: `${window.location.pathname}`,
						title: `${this.config.title}`,
					},
				},
			});
		});
	}

	/**
	 * Use the Sourcepoint (tcfapi) to handle user consent
	 */
	initSourcepoint(type: string) {
		// Add the pageview event if the tcfapi is loaded or when a user has changed something in their consent settings
		window.__tcfapi(
			'addEventListener',
			2,
			(tcData: { eventStatus: string }) => {
				if (
					tcData.eventStatus === 'tcloaded' ||
					tcData.eventStatus === 'useractioncomplete'
				) {
					if (this.event_type === 'article_view') {
						console.log( 'article view');
						this.element.addEventListener('copy', this.addCopyText.bind(this));
						this.element.addEventListener('cut', this.addCopyText.bind(this));
						this.trackScrollPercentage();
						// add read percenbtage tracker
					}
				}
			},
		);
	}

	addCopyText() {
		window.dataLayer.push({
			event: 'copy_text',
			eventInfo: {
				category: 'user interactions',
				action: 'copy text',
				label: `${this.config.id}`,
				value: null,
				event_context: {
					article_id: `${this.config.id}`,
				},
			},
		});
	}

	trackTeaserInViewport() {
		if ( this.elementIsVisibleInViewport( this.element ) && this.do_not_track === false ) {
			this.addArticleImpression();
			this.do_not_track = true;
		}
	}

	elementIsVisibleInViewport( element: HTMLElement, partiallyVisible = false ) {
		const { top, left, bottom, right } = element.getBoundingClientRect();
		const { innerHeight, innerWidth } = window;
		return partiallyVisible
			? ( ( top > 0 && top < innerHeight ) ||
			( bottom > 0 && bottom < innerHeight ) ) &&
			( ( left > 0 && left < innerWidth ) || ( right > 0 && right < innerWidth ) )
			: top >= 0 && left >= 0 && bottom <= innerHeight && right <= innerWidth;
	};

	addArticleImpression() {
		window.dataLayer.push({
			event: 'article_impression',
			event_info: {
				category: 'user interaction',
				action: 'view item list',
				label: `${this.config.id}`,
				value: null,
				event_context: {
					component: 'article',
					component_subtype: `${this.config.component_subtype}`,
					section_title: `${this.config.parent_component}`,
				},
				ecommerce: {
					article: [
						{
							article_id: `${this.config.id}`,
							article_name: `${this.config.title}`,
							article_category: `${this.config.categories}`,
							article_type: 'article',
							author_name: `${this.config.author}`,
							article_publication_date: `${this.config.publication_date}`,
							article_publication_time: `${this.config.publication_time}`,
							article_version: `${this.config.revisions_count}`,
							article_index: `${this.config.article_index}`,
						},
					],
				},
			},
		});
	}

	addArticleClick() {
		window.dataLayer.push({
			event: 'article_click',
			event_info: {
				category: 'user interactions',
				action: 'article click',
				label: `${this.config.id}`,
				value: null,
				component: {
					component: 'article',
					component_subtype: `${this.config.component_subtype}`,
				},
				ecommerce: {
					article: [
						{
							article_id: `${this.config.id}`,
							article_name: `${this.config.title}`,
							article_category: 'partner content',
							article_type: 'article',
							author_name: `${this.config.author}`,
							article_label: `${this.config.id}`,
							article_publication_date: `${this.config.publication_date}`,
							article_publication_time: `${this.config.publication_time}`,
							article_version: `${this.config.revisions_count}`,
							article_index: `${this.config.article_index}`,
						},
					],
				},
			},
		});
	}

	addShareArticleShareEvent() {
		console.log( 'share article event' );
		window.dataLayer.push({
			event: 'share_article',
			event_info: {
				category: 'user interactions',
				action: 'share article',
				label: `${this.config.share_type}`,
				value: null,
				event_context: {
					article_id: `${this.config.id}`,
					share_type: `${this.config.share_type}`,
				},
			},
		});
	}

	addArticleViewEvent() {
		window.dataLayer.push({
			event: 'article_view',
			event_info: {
				category: 'user interactions',
				action: 'article view',
				label: `${this.config.id}`,
				value: null,
				article: [
					{
						article_id: `${this.config.id}`,
						article_name: `${this.config.title}`,
						article_category: 'partner content',
						article_type: 'article',
						author_name: `${this.config.author}`,
						article_label: 'partner content',
						article_publication_date: `${this.config.publication_date}`,
						article_publication_time: `${this.config.publication_time}`,
						article_version: `${this.config.revisions_count}`,
						article_index: '1',
					},
				],
			},
		});
	}

	trackScrollPercentage() {
		window.addEventListener('scroll', () => {
			const scrollHeight = document.documentElement.scrollHeight - window.innerHeight;
			const scrollTop = window.scrollY;
			let scrollPercentage = Math.floor((scrollTop / scrollHeight) * 100);

			// Ensure that the scroll percentage doesn't exceed 100%
			scrollPercentage = Math.min(scrollPercentage, 100);

			// Add percentages below the current scroll position if page is opened at a high percentage
			if (!this.pushedScrollPercentages.size && scrollPercentage < 100) {
				for (let i = 0; i <= scrollPercentage; i += 25) {
					this.pushedScrollPercentages.add(i);
					this.addScrollEvent(i);
				}
			}

			// Calculate nearest multiple of 25%
			const nearestMultipleOf25 = Math.ceil(scrollPercentage / 25) * 25;

			// Push events to dataLayer at specific scroll percentages only if not pushed before
			if (nearestMultipleOf25 <= 100 && !this.pushedScrollPercentages.has(nearestMultipleOf25)) {
				this.pushedScrollPercentages.add(nearestMultipleOf25);
				this.addScrollEvent(nearestMultipleOf25);
			}
		});
	}

	addScrollEvent( scrollPercentage: number ) {
		window.dataLayer.push({
			event: 'scroll_depth',
			event_info: {
				category: 'user interactions',
				action: 'scroll depth',
				label: `${scrollPercentage}`,
				value: null,
				event_context: {
					article_id: `${this.config.id}`,
					page_notification: null,
				},
			},
		});
	}

	initCookieConsent() {
		window.__tcfapi(
			'addEventListener',
			2,
			(tcData: { eventStatus: string }) => {
				if (tcData.eventStatus === 'cmpuishown') {
					window.dataLayer.push({
						event: 'cmp_banner',
						event_info: {
							category: 'consent',
							action: 'cmp banner shown',
							label: 'yes',
							value: null,
							event_context: [
								{
									hostname: `${window.location.hostname}`,
									pathname: `${window.location.pathname}`,
									referrer: `${document.referrer}`,
									title: `${this.config.title}`,
									article_id: `${this.config.id}`,
									cmp_shown: 'yes',
								},
							],
						},
					});
				}
			},
		);
	}

	addCookieConsentAcceptEvent() {
		window.__tcfapi(
			'addEventListener',
			2,
			(tcData: { eventStatus: string; purpose: any }) => {
				if (tcData.eventStatus === 'useractioncomplete') {
					const consentLevel = tcData.purpose.consents[2]
						? 'accept_all_cookies'
						: 'accept_selection';
					window.dataLayer.push({
						event: 'consent_level',

						event_info: {
							category: 'consent',
							action: 'consent level',
							label: `${consentLevel}`, // accept_all_cookies | accept_selection
							value: null,
							event_context: [
								{
									hostname: `${window.location.hostname}`,
									pathname: `${window.location.pathname}`,
									title: `${this.config.title}`,
									category: `category`, // e.g.
								},
							],
						},
					});
				}
			},
		);
	}
}

export default DataLayer;
