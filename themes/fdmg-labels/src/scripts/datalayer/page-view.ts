import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
	const pathName = window.location.pathname.split('/').join(''); //remove slashes from pathname
	let pageType = config.page_type;
	let pageSubType = config.page_subtype;

	if ( config.page_type === 'page' ) {
		switch ( pathName ) {
			case 'contact-us':
				pageType = 'contact';
				pageSubType = 'contact us';
				break;
			case 'about':
				pageType = 'about';
				pageSubType = 'about impact investor';
				break;
			case 'guide-2023':
				pageType = 'reports';
				pageSubType = 'guide 2023';
				break;
			case 'guide-2024':
				pageType = 'reports';
				pageSubType = 'guide 2024';
				break;
			case 'impact-investing-for-pensions-2023':
				pageType = 'reports';
				pageSubType = 'impact investing for pensions';
				break;
			case 'events':
				pageType = 'events';
				pageSubType = 'other events';
				break;
			case 'partners':
				pageType = 'partners';
				pageSubType = 'partners';
				break;
			case 'share-news-tip':
				pageType = 'support';
				pageSubType = 'share news tip';
				break;
			case 'newsletter-header-and-widget':
				pageType = 'newsletter';
				pageSubType = 'sign up';
				break;
			case 'meet-our-board-of-advisors':
				pageType = 'support';
				pageSubType = 'meet our board of investors';
				break;
		}
	}

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
				title: `${config.title}`,
				category: `${config.page_category}`,
				page_type: `${ pageType }`,
				page_sub_type: `${ pageSubType }`,
			},
		},
	});
};

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
				pushToDataLayer(config);
			}
		},
	);
}

const handlePageView = (config: DataLayerConfig) => {
	pushToDataLayer(config);
};

export default handlePageView;
