import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
	window.dataLayer.push({
		event: 'share_article',
		event_info: {
			category: 'user interactions',
			action: 'share article',
			label: `${config.share_type}`,
			value: null,
			event_context: {
				article_id: `${config.id}`,
				share_type: `${config.share_type}`,
			},
		},
	});
}

const handleShareArticle = (config: DataLayerConfig) => {
	const { element } = config;

	element.addEventListener('click', () => {
		pushToDataLayer(config);
	});
};

export default handleShareArticle;
