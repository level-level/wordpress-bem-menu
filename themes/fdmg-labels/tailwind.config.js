const plugin = require('tailwindcss/plugin'); // eslint-disable-line

/** @type {import('tailwindcss').Config} */

const customThemes = ['theme-energeia', 'theme-pensioen-pro'];

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
			'primary-300': 'var(--color-primary-300)',
			'base-50': 'var(--base-50)',
			'base-100': 'var(--base-100)',
			'base-200': 'var(--base-200)',
			'base-300': 'var(--base-300)',
			'base-400': 'var(--base-400)',
			'base-500': 'var(--base-500)',
			'base-600': 'var(--base-600)',
			'base-700': 'var(--base-700)',
			'base-800': 'var(--base-800)',
		},
		fontFamily: {
			body: ['"ProximaNovaRegular"', 'sans-serif'],
			bold: ['"ProximaNovaBold"', 'sans-serif'],
			heading: ['"Libre Caslon Text"', 'sans-serif'],
		},
		fontSize: {
			sm: [
				'0.875rem',
				{
					lineHeight: '1.125rem',
				},
			],
			base: [
				'1rem',
				{
					lineHeight: '1.375rem',
				},
			],
			md: [
				'1.125rem',
				{
					lineHeight: '1.5rem',
				},
			],
			lg: [
				'1.375rem',
				{
					lineHeight: '1.875rem',
				},
			],
			xl: [
				'1.875rem',
				{
					lineHeight: '2.625rem',
				},
			],
			'2xl': [
				'2.625rem',
				{
					lineHeight: '3.5rem',
				},
			],
			'3xl': [
				'3.625rem',
				{
					lineHeight: '4.5rem',
				},
			],
		},
	},
	plugins: [
		plugin(function ({ addVariant, e }) {
			addVariant('hocus', ['&:hover', '&:focus-visible', '&:focus-within']);
			addVariant('image', '& > img');
			addVariant('iframe', '& > iframe');
			addVariant('svg', '& > svg');

			customThemes.forEach((theme) => {
				addVariant(`${theme}`, ({ modifySelectors, separator }) => {
					modifySelectors(({ className }) => {
						return `.${theme} .${e(`${theme}${separator}${className}`)}`;
					});
				});
			});
		}),
	],
};
