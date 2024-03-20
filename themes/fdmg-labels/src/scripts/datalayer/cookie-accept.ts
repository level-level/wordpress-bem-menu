import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig, tcData) => {
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
					title: `${config.title}`,
					category: `category`, // e.g.
				},
			],
		},
	});
}

const handleArticleClick = (config: DataLayerConfig) => {
	window.__tcfapi(
		'addEventListener',
		2,
		(tcData: { eventStatus: string; purpose: any }) => {
			if (tcData.eventStatus === 'useractioncomplete') {
				pushToDataLayer(config, tcData);
			}
		},
	);
};

export default handleArticleClick;
