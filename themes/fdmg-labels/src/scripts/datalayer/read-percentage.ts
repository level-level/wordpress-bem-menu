import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig, readPercentage: number) => {
	window.dataLayer.push({
		event: 'read_percentage',
		event_info: {
			category: 'user interactions',
			action: 'read percentage',
			label: `${readPercentage}`,
			value: null,
			event_context: {
				article_id: `${config.id}`,
				page_notification: null,
			},
		},
	});
}

/**
 * Get the total word count of the article
 * @returns The total word count of the article pls the article title minus figures and divs with data-name containing 'acf'
 */
const getArticleWordcount = (config: DataLayerConfig) => {
	let totalWords = 0;

	// Count words in the title
	const title = document.querySelector('h1');
	const titleContent = title?.textContent?.trim();
	const titleWords = titleContent?.split(/\s+/).filter( ( word ) => {
		return word.length > 0;
	});

	totalWords += titleWords?.length ?? 0;

	// Count words in the article content
	const article = document.querySelector('.entry-content');

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

	return totalWords;
}

/**
	 * Track the reading percentage of the article based on the wordcount and average reading speed
	 */
const trackReadPercentage = ( config: DataLayerConfig ) => {
	const averageReadingSpeed = 200; // Assume 200 words per minute for average reading speed
	const wordCount = getArticleWordcount(config);
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

		// Loop through percentage values
		percentageValues.forEach(percentage => {
			// Calculate the time to reach the given percentage of the reading
			const timeToReachPercentage = (percentage / 100) * expectedReadingTimeInSeconds;

			if (activeTime >= timeToReachPercentage) {
				// Add event to dataLayer
				pushToDataLayer(config, percentage);

				// Remove the percentage from the array so it won't be tracked again
				percentageValues = percentageValues.filter(value => value !== percentage);
			}
		});

		// Check if all percentage values have been reached
		if (percentageValues.length === 0) {
			// Clear the interval if all percentage values have been reached
			clearInterval(intervalId);
		}
	}, 1000);
}

/**
 * Use the Sourcepoint (tcfapi) to handle user consent
 */
const initSourcepoint = (config: DataLayerConfig) => {
	// Add the pageview & article_view event if the tcfapi is loaded or when a user has changed something in their consent settings
	window.__tcfapi(
		'addEventListener',
		2,
		(tcData: { eventStatus: string }) => {
			if (
				tcData.eventStatus === 'tcloaded' ||
				tcData.eventStatus === 'useractioncomplete'
			) {
				trackReadPercentage(config);
			}
		},
	);
}

const handleReadPercentage = (config: DataLayerConfig) => {
	if ( typeof window.__tcfapi !== 'undefined' ) {
		initSourcepoint ( config );
	} else {
		trackReadPercentage(config);
	}
};

export default handleReadPercentage;
