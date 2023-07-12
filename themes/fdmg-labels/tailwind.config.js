const plugin = require('tailwindcss/plugin'); // eslint-disable-line

/** @type {import('tailwindcss').Config} */
module.exports = {
	content: ['./templates/**/*.twig', './src/scripts/**/*.{tsx, ts}'],
	darkMode: ['class', '[data-theme="dark"]'],
	theme: {
		extend: {},
		screens: {
			sm: '768px',
			md: '992px',
			lg: '1200px',
			xl: '1400px',
		},
		spacing: {
			auto: 'auto',
			0: '0px',
			1: '4px',
			2: '8px',
			3: '12px',
			4: '16px',
			5: '20px',
			6: '24px',
			7: '28px',
			8: '32px',
			9: '36px',
			10: '40px',
			11: '44px',
			12: '48px',
			13: '52px',
			14: '56px',
			15: '60px',
			16: '64px',
		},
		colors: {
			transparent: 'transparent',
			primary: 'var(--color-primary)',
			secondary: 'var(--color-secondary)',
		},
	},
	plugins: [
		plugin(function ({ addVariant, e }) {
			addVariant('hocus', ['&:hover', '&:focus-visible', '&:focus-within']);
			addVariant('image', '& > img');
			addVariant('iframe', '& > iframe');
			addVariant('svg', '& > svg');
			addVariant('theme-energeia', ({ modifySelectors, separator }) => {
				modifySelectors(({ className }) => {
					return `.theme-energeia .${e(`theme-energeia${separator}${className}`)}`;
				});
			});
			addVariant('theme-pensioen-pro', ({ modifySelectors, separator }) => {
				modifySelectors(({ className }) => {
					return `.theme-pensioen-pro .${e(`theme-pensioen-pro${separator}${className}`)}`;
				});
			});
		}),
	],
};
