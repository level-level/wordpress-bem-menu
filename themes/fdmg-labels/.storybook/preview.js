import { themes } from '@storybook/theming';
import '../src/styles/main.scss';

let localConfig = '';
if ( process.env.NODE_ENV === 'development' ){
	localConfig = require( '../config.local.json' );
}

const storybook = require( '@level-level/storybook-config' )( localConfig );

export const parameters = {
	server: storybook.server,
	controls: {
		hideNoControlsWarning: true,
		expanded: false,
	},
	darkMode: {
		darkClass: 'theme-dark',
		lightClass: 'theme-light',
		classTarget: 'html',
		stylePreview: true,
		dark: {
			...themes.dark,
		},
		light: {
			...themes.normal,
		}
	}
};
