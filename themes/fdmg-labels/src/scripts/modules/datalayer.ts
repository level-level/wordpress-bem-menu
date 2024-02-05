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
		page_category: string;
		page_type: string;
		page_subtype: string;
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
		if ( this.event_type === 'article_view' ) {
			if ( typeof window.__tcfapi !== 'undefined' ) {
				this.initSourcepoint ( 'article_view' );
			} else {
				this.element.addEventListener('copy', this.addCopyText.bind(this));
				this.element.addEventListener('cut', this.addCopyText.bind(this));
				this.trackScrollPercentage();
				this.trackReadPercentage();
			}
		}
		if ( this.event_type === 'pageview' ) {
			if ( typeof window.__tcfapi !== 'undefined' ) {
				this.initSourcepoint ( 'pageview' );
			} else {
				this.addPageview();
			}
		}
	}

	/**
	 * Use the Sourcepoint (tcfapi) to handle user consent
	 */
	initSourcepoint(type: string) {
		// Add the pageview & article_view event if the tcfapi is loaded or when a user has changed something in their consent settings
		window.__tcfapi(
			'addEventListener',
			2,
			(tcData: { eventStatus: string }) => {
				if (
					tcData.eventStatus === 'tcloaded' ||
					tcData.eventStatus === 'useractioncomplete'
				) {
					if (this.event_type === 'article_view') {
						this.element.addEventListener('copy', this.addCopyText.bind(this));
						this.element.addEventListener('cut', this.addCopyText.bind(this));
						this.trackScrollPercentage();
						this.trackReadPercentage();
					}
					if (this.event_type === 'pageview') {
						this.addPageview();
					}
				}
			},
		);
	}

	/**
	 * Add a pageview event to the dataLayer
	 */
	addPageview()	{
		window.dataLayer.push({
			event: 'page_view',
			eventInfo: {
				category: 'pageviews',
				action: 'pageview',
				label: `${window.location.pathname}`,
				value: null,
				event_context: {
					hostname: `${window.location.hostname}`,
					pathname: `${window.location.pathname}`,
					referrer: `${document.referrer}`,
					search: `${window.location.search}`,
					title: `${this.config.title}`,
					category: `${this.config.page_category}`,
					page_type: `${this.config.page_type}`,
					page_sub_type: `${this.config.page_subtype}`,
				},
			},
		});
	}

	/**
	 * Add a copy text event to the dataLayer
	 */
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

	/**
	 * Track the article teaser in the viewport
	 */
	trackTeaserInViewport() {
		// Only track the first time the element is in the viewport (based on do_not_track flag)
		if ( this.elementIsVisibleInViewport( this.element ) && this.do_not_track === false ) {
			this.addArticleImpression();
			this.do_not_track = true;
		}
	}

	/**
	 * Check if the element is (partially) in the viewport
	 * @param element
	 * @param partiallyVisible
	 * @returns boolean
	 */
	elementIsVisibleInViewport( element: HTMLElement, partiallyVisible = false ) {
		const { top, left, bottom, right } = element.getBoundingClientRect();
		const { innerHeight, innerWidth } = window;
		return partiallyVisible
			? ( ( top > 0 && top < innerHeight ) ||
			( bottom > 0 && bottom < innerHeight ) ) &&
			( ( left > 0 && left < innerWidth ) || ( right > 0 && right < innerWidth ) )
			: top >= 0 && left >= 0 && bottom <= innerHeight && right <= innerWidth;
	};

	/**
	 * Add article impression event to the datalayer
	 */
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

	/**
	 * Add an article click event to the dataLayer
	 */
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

	/**
	 * Add a share article event to the dataLayer
	 */
	addShareArticleShareEvent() {
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

	/**
	 * Track the reading percentage of the article based on the wordcount and average reading speed
	 */
	trackReadPercentage() {
		const averageReadingSpeed = 200; // Assume 200 words per minute for average reading speed
		const wordCount = this.getArticleWordcount();
		const expectedReadingTimeInSeconds = (wordCount / averageReadingSpeed) * 60;
		const inactivityThresholdInSeconds = 30;
		const userActivityEvents = ['scroll', 'mousemove', 'click', 'touchstart', 'keydown'];

		let percentageValues = [25, 50, 75, 100]; // Percentages to track
		let interaction = false;
		let activeTime = 0;
		let lastInteraction = Date.now();

		const setInteracting = () => {
			interaction = true;
			lastInteraction = Date.now();
		};

		userActivityEvents.forEach(event => {
			document.addEventListener(event, setInteracting);
		});

		const intervalId = setInterval(() => {
			// Check if there actually was an interaction
			if (interaction === false || document.hidden) return;

			// Reset interaction if last interaction was longer than 30 seconds ago
			if (Date.now() - lastInteraction > inactivityThresholdInSeconds * 1000) {
				interaction = false;
			}

			// Increase active time by 1 second
			activeTime++;

			console.log(activeTime);

			// Loop through percentage values
			percentageValues.forEach(percentage => {
				// Calculate the time to reach the given percentage of the reading
				const timeToReachPercentage = (percentage / 100) * expectedReadingTimeInSeconds;

				if (activeTime >= timeToReachPercentage) {
					// Add event to dataLayer
					this.addReadPercentageEvent(percentage);
					console.log(`User has reached ${percentage}% of the reading.`);

					// Remove the percentage from the array so it won't be tracked again
					percentageValues = percentageValues.filter(value => value !== percentage);
				}
			});

			// Check if all percentage values have been reached
			if (percentageValues.length === 0) {
				// Clear the interval if all percentage values have been reached
				clearInterval(intervalId);
				console.log("Reached 100% reading. Stopping interval.");
			}
		}, 1000);
	}

	/**
	 * Get the total word count of the article
	 * @returns The total word count of the article pls the article title minus figures and divs with data-name containing 'acf'
	 */
	getArticleWordcount() {
		let totalWords = 0;

		// Count words in the title
		const title = this.element.querySelector('h1');
		const titleContent = title?.textContent?.trim();
		const titleWords = titleContent?.split(/\s+/).filter( ( word ) => {
			return word.length > 0;
		});

		totalWords += titleWords?.length ?? 0;

		// Count words in the article content
		const article = this.element.querySelector('.entry-content');

		if( article ) {
			Array.from( article.children ).forEach(function(child) {

				// Check if the child is a div with data-name containing 'acf' or a figure
				if ( (child.getAttribute('data-name') && child.getAttribute('data-name')?.includes('acf') ) ||
					child.tagName.toLowerCase() === 'figure') {
					// Skip counting words in divs with data-name containing 'acf' and figures
					return;
				}

				// Count words in the text content of the child element
				const textContent = child.textContent?.trim();
				const words = textContent?.split(/\s+/).filter( (word) => {
					return word.length > 0; // Filter out empty strings
				});

				// Add the number of words to the total
				totalWords += words?.length ?? 0;
			});
		}

		console.log(`Total word count: ${totalWords}`);

		return totalWords;
	}

	/**
	 * Add a read percentage event to the dataLayer
	 * @param readPercentage
	 */
	addReadPercentageEvent( readPercentage: number ) {
		window.dataLayer.push({
			event: 'read_percentage',
			event_info: {
				category: 'user interactions',
				action: 'read percentage',
				label: `${readPercentage}`,
				value: null,
				event_context: {
					article_id: `${this.config.id}`,
					page_notification: null,
				},
			},
		});
	}

	/**
	 * Track the scroll percentage of the article
	 */
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

	/**
	 * Add a scroll event to the dataLayer
	 * @param scrollPercentage
	 */
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

	/**
	 * Add datalayer event for cookie banner shown
	 */
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

	/**
	 * Add datalayer event for cookie consent accepted
	 */
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
