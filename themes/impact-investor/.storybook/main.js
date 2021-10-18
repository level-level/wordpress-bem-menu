const config = {
	addons: [],
}

const storybook = require( '@level-level/storybook-config' )( {}, config );
module.exports = storybook.config;
