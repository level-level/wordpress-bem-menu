import { DataLayerConfig } from '../../globals';
import handleArticleClick from './article-click';
import handleArticleImpression from './article-impression';
import handlePageView from './page-view';
import handleReadPercentage from './read-percentage';
import handleScrollDepth from './scroll-depth';
import handleCopyText from './copy';
import handleShareArticle from './share-article';
import handleClicks from './click-events';

enum EventType {
	Teaser = 'TEASER',
	Pageview = 'PAGEVIEW',
	Share = 'SOCIAL_SHARE',
}

const initializeGlobalEvents = () => {
	// Place global events here like link clicks, cut, copy etc.
	handleClicks();
};

const dataLayer = () => {
	window.dataLayer = [];
	const elements = Array.from(document.querySelectorAll('[data-datalayer]'));
	const configs: DataLayerConfig[] = elements.map((el) => {
		const rawData = el.getAttribute('data-datalayer');
		const parsedData = rawData ? JSON.parse(rawData) : {};
		parsedData.element = el;

		return parsedData;
	});

	configs.forEach((config: DataLayerConfig) => {
		const { event } = config;

		switch (event) {
			case EventType.Teaser:
				handleArticleImpression(config);
				handleArticleClick(config);
				break;
			case EventType.Pageview:
				handlePageView(config);
				if(config.id) {
					handleReadPercentage(config);
					handleScrollDepth(config);
					handleCopyText(config);
				}
				break;
			case EventType.Share:
				handleShareArticle(config);
				break;
			default:
				break;
		}
	});

	initializeGlobalEvents();
};

export default dataLayer;