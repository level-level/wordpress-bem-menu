/*
 * For a detailed explanation regarding each configuration property, visit:
 * https://jestjs.io/docs/configuration
 */

module.exports = {
	clearMocks: true,
	testEnvironment: 'jsdom',
	globals: {
		icons: {
			arrowDown: true,
		},
		i18n: {
			navigation: {
				subMenu: true,
			},
		},
	},
};
