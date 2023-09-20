const isDevelopment = process.env.NODE_ENV === 'development';
const options = { configOnly: true };
let customConfig = require('@level-level/webpack-config/src/build.js')(options);

if (isDevelopment) {
	customConfig = require('@level-level/webpack-config/src/dev.js')(options);
}

/**
 * Use our custom webpack config rules,
 * we need the tailwindcss and postcss configs.
 */
module.exports = async ({ config }) => {
	if (!isDevelopment) {
		// Make sure the MiniCssExtractPlugin is add as a plugin, because is used in the webpack prod rules.
		config.plugins.push(customConfig.plugins.pop());
	}

	return {
		...config,
		module: { ...config.module, rules: customConfig.module.rules },
	};
};
