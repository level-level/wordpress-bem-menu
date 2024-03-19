import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
	window.dataLayer.push({
		event: 'copy_text',
		eventInfo: {
			category: 'user interactions',
			action: 'copy text',
			label: `${config.id}`,
			value: null,
			event_context: {
				article_id: `${config.id}`,
			},
		},
	});
}

const handleCopyText = (config: DataLayerConfig) => {
	const { element } = config;

	element.addEventListener('copy', () => {
		pushToDataLayer(config);
	});

	element.addEventListener('cut', () => {
		pushToDataLayer(config);
	});
};

export default handleCopyText;
