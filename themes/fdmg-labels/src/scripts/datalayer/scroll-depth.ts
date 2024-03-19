import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig, scrollPercentage: number ) => {
	window.dataLayer.push({
		event: 'scroll_depth',
		event_info: {
			category: 'user interactions',
			action: 'scroll depth',
			label: `${scrollPercentage}`,
			value: null,
			event_context: {
				article_id: `${config.id}`,
				page_notification: null,
			},
		},
	});
}

const handleScrollDepth = (config: DataLayerConfig) => {
	
	const contentElement = document.querySelector('main');

	if (!contentElement) return;

	let lastTriggeredPercentage = 0;

	const calculateReadPercentage = () => {
		const { top, height } = contentElement.getBoundingClientRect();
		const scrolled = window.scrollY - top;
		const scrollPercentage = Math.max(0, Math.min((scrolled / height) * 100, 100));

		return Math.round(scrollPercentage);
	};

	window.addEventListener('scroll', () => {
		const percentage = calculateReadPercentage();
		const nextTrigger = [25, 50, 75, 100].find((p) => p > lastTriggeredPercentage && p <= percentage);
		if (nextTrigger !== undefined) {
			pushToDataLayer(config, nextTrigger);
			lastTriggeredPercentage = nextTrigger;
		}
	});
};

export default handleScrollDepth;
