const plugin = require('tailwindcss/plugin'); // eslint-disable-line

/** @type {import('tailwindcss').Config} */

const customThemes = [
	{
		theme: 'theme-impact-investor',
		name: 'impact-investor',
		logos: {
			default: '../images/logo-impact-investor.svg',
			darkMode: '../images/logo-impact-investor-darkmode.svg',
			white: '../images/logo-impact-investor-white.svg',
		},
	},
	{
		theme: 'theme-energeia',
		name: 'energeia',
		logos: {
			default: '../images/logo-energeia.svg',
			darkMode: '../images/logo-energeia-darkmode.svg',
		},
	},
	{
		theme: 'theme-pensioen-pro',
		name: 'pensioen-pro',
		logos: {
			default: '../images/logo-pensioen-pro.svg',
			darkMode: '../images/logo-pensioen-pro-darkmode.svg',
		},
	},
];

const themeLogos = {};

for (const theme of customThemes) {
	themeLogos[`logo-${theme.name}`] = `url('${theme.logos.default}')`;
	themeLogos[`logo-${theme.name}-darkmode`] = `url('${theme.logos.darkMode}')`;
	if (theme.logos.white) {
		themeLogos[`logo-${theme.name}-white`] = `url('${theme.logos.white}')`;
	}
}

module.exports = {
	content: ['./templates/**/*.twig', './src/scripts/**/*.{tsx,ts}'],
	darkMode: ['class', '[data-theme="dark"]'],
	theme: {
		extend: {
			spacing: {
				'admin-bar': 'var(--wp-admin--admin-bar--height, 0px)',
				container: 'var(--container)',
				'header-height': 'var(--header-height)',
				'menu-height': 'var(--menu-height)',
				'sidebar-width': 'var(--sidebar-width)',
				'article-width': 'var(--article-width)',
				'article-offset': 'var(--article-offset)',
				'content-width': 'var(--content-width)',
			},
			boxShadow: {
				header: '0 4px 16px rgb(0 0 0 / 10%)',
				teaser: '0 2px 4px rgb(0 0 0 / 15%)',
			},
			backgroundImage: {
				...themeLogos,
			},
			transitionTimingFunction: {
				'theme-bezier': 'cubic-bezier(0.2, 0.91, 0.53, 1)',
			},
		},
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
			primary: 'var(--primary)',
			'primary-300': 'var(--primary-300)',
			'base-50': 'var(--base-50)',
			'base-100': 'var(--base-100)',
			'base-200': 'var(--base-200)',
			'base-300': 'var(--base-300)',
			'base-400': 'var(--base-400)',
			'base-500': 'var(--base-500)',
			'base-600': 'var(--base-600)',
			'base-700': 'var(--base-700)',
			'base-800': 'var(--base-800)',
			'secondary-50': 'var(--secondary-50)',
			'secondary-100': 'var(--secondary-100)',
			'secondary-200': 'var(--secondary-200)',
			'secondary-300': 'var(--secondary-300)',
			'secondary-400': 'var(--secondary-400)',
			'secondary-500': 'var(--secondary-500)',
			'secondary-600': 'var(--secondary-600)',
			'secondary-700': 'var(--secondary-700)',
		},
		fontFamily: {
			body: ['"ProximaNovaRegular"', 'sans-serif'],
			'body-bold': ['"ProximaNovaBold"', 'sans-serif'],
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
			addVariant('owl', '& > * + *');
			addVariant('ul', '& > ul');
			addVariant('ol', '& > ol');
			addVariant('ulli', '& > ul li');

			addVariant('btn-text', ({ modifySelectors, separator }) => {
				modifySelectors(({ className }) => {
					const modifiedClassName = e(`btn-text${separator}${className}`);

					return [
						`.${modifiedClassName} .btn__text`,
						`.${e(`group-hover${separator}${modifiedClassName}`)}:hover .btn__text`,
						`.${modifiedClassName}:focus .btn__text`,
						`.${modifiedClassName}:focus-within .btn__text`,
					];
				});
			});

			addVariant('btn-icon', ({ modifySelectors, separator }) => {
				modifySelectors(({ className }) => {
					const modifiedClassName = e(`btn-icon${separator}${className}`);

					return [
						`.${modifiedClassName} .btn__icon`,
						`.${e(`group-hover${separator}${modifiedClassName}`)}:hover .btn__icon`,
						`.${modifiedClassName}:focus .btn__icon`,
						`.${modifiedClassName}:focus-within .btn__icon`,
					];
				});
			});

			addVariant(`compact-header`, ({ modifySelectors, separator }) => {
				modifySelectors(({ className }) => {
					return `.has-compact-header .${e(`compact-header${separator}${className}`)}`;
				});
			});

			customThemes.forEach((theme) => {
				if (theme.theme !== 'theme-impact-investor') {
					addVariant(`${theme.theme}`, ({ modifySelectors, separator }) => {
						modifySelectors(({ className }) => {
							return `.${theme.theme} .${e(`${theme.theme}${separator}${className}`)}`;
						});
					});
				}
			});
		}),
	],
};
