import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
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
					title: `${config.title}`,
					article_id: `${config.id}`,
					cmp_shown: 'yes',
				},
			],
		},
	});
}

const handleCookieBannerShown = (config: DataLayerConfig) => {
	window.__tcfapi(
		'addEventListener',
		2,
		(tcData: { eventStatus: string }) => {
			if (tcData.eventStatus === 'cmpuishown') {
				pushToDataLayer(config);
			}
		},
	);
};

export default handleCookieBannerShown;
