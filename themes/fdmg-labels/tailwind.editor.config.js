// eslint-disable-next-line @typescript-eslint/no-var-requires, no-undef
const defaultConfig = require('./tailwind.config');

module.exports = {
	...defaultConfig,
	...{
		important: '.editor-styles-wrapper',
	},
};
