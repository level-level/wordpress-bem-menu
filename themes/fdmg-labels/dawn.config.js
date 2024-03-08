module.exports = {
	entries: {
		main: './src/index.ts',
		admin: './src/admin.ts',
		editor: './src/editor.ts',
		sentry: './src/sentry.ts',
		head: './src/head.ts',
	},
	dist: './dist',
	src: './src',
	sentry: {
		project: 'fdmg-labels-fe',
		urlPrefix: '~/wp-content/themes/fdmg-labels/dist/',
	},
	webpack({ module }) {},
	webpackDev({ mode, devtool }) {},
	webpackProd({ mode, devtool }) {},
	useTailwindCSS: true,
};
