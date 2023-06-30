const customConfig = require('@level-level/webpack-config/src/dev.js')({
	configOnly: true,
});

module.exports = async ({ config }) => {
	if( process.env.NODE_ENV === 'development' ){
		return { ...config, module: { ...config.module, rules: customConfig.module.rules } };
	}

	return config;
};
