module.exports = {
	root: true,
	extends: ['@level-level/eslint-config-level-level', 'prettier'],

	globals: {
		lodash: true,
		localStorage: true,
		__tcfapi: true,
		icons: true,
	},

	rules: {
		'@typescript-eslint/no-explicit-any': 'off',
	},

	env: {
		browser: true,
	},
};
