const config = {
	addons: ['storybook-dark-mode'],
};

const storybook = require('@level-level/storybook-config')({}, config);
module.exports = storybook.config;
