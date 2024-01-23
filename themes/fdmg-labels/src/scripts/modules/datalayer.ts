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
	};

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config
			? JSON.parse(this.element.dataset.config)
			: {};
		this.initialize();
	}

	initialize() {
		if (this.config.type === 'pageview') {
			if (typeof window.__tcfapi !== 'undefined') {
				this.initSourcepoint('pageview');
			} else {
				this.addPageViewEvent();
			}
		}
		if (this.config.type === 'articleview') {
			if (typeof window.__tcfapi !== 'undefined') {
				this.initSourcepoint('articleview');
			} else {
				this.addArticleViewEvent();
			}
		}
		if (this.config.type === 'article_click') {
			this.element.addEventListener('click', this.addArticleClick.bind(this));
		}
		if (this.config.type === 'outbound_click') {
			this.element.addEventListener('click', this.addOutBoundClick.bind(this));
		}
		if (this.config.type === 'cookiebanner_shown') {
			this.initCookieConsent();
			this.addCookieConsentAcceptEvent();
		}

		if (this.config.type === 'active_time_on_page') {
			this.setupActivityTracker();
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
					if (type === 'pageview') {
						this.addPageViewEvent();
					} else if (type === 'articleview') {
						this.addArticleViewEvent();
					}
				}
			},
		);
	}

	addOutBoundClick() {
		window.dataLayer.push({
			event: 'outbound_click',
			eventInfo: {
				category: 'external click',
				action: 'link click',
				label: `${this.config.title}`,
				value: `${this.config.url}`,
				event_context: {
					component: `${this.config.parent_component}`,
					click_text: `${this.config.title}`,
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

	addPageViewEvent() {
		window.dataLayer.push({
			event: 'page_view',
			event_info: {
				category: 'pageviews',
				action: 'pageview',
				label: `${window.location.pathname}`,
				value: null,
				event_context: [
					{
						hostname: `${window.location.hostname}`,
						pathname: `${window.location.pathname}`,
						referrer: `${document.referrer}`,
						search: '',
						title: `${this.config.title}`,
						category: 'partner content',
						page_notification: null,
						page_type: 'partner pagina',
						page_sub_type: 'partner pagina',
					},
				],
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

		setTimeout(() => {
			window.dataLayer.push({
				event: 'quality_read',
				event_info: {
					category: 'user interaction',
					action: 'quality read',
					label: `${this.config.id}`,
					value: null,
					event_context: [
						{
							hostname: `${window.location.hostname}`,
							pathname: `${window.location.pathname}`,
							title: `${this.config.title}`,
							category: 'partner content',
						},
					],
				},
			});
		}, 15000);
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
