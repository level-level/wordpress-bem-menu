import '@fontsource/libre-caslon-text/400.css';
import '@fontsource/libre-caslon-text/400-italic.css';
import '@fontsource/libre-caslon-text/700.css';
import '../src/styles/main.scss';

let localConfig = '';
if (process.env.NODE_ENV === 'development') {
	localConfig = require('../config.local.json');
}

const storybook = require('@level-level/storybook-config')(localConfig);

export const parameters = {
	server: storybook.server,
	controls: {
		hideNoControlsWarning: true,
		expanded: false,
	},
};
