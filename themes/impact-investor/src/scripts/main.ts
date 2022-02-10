import './utilities/user-tabs';
import ThemeSelection from './modules/theme-selection';
import PrimaryNav from './modules/primary-nav';
import HeaderMenu from './modules/header-menu';
import HeaderSearch from './modules/header-search';
import HeaderProfile from './modules/header-profile';
import DisplayOptions from './modules/display-options';
import Skiplink from './modules/skiplink';
import OEmbed from './modules/oembed';
import Table from './modules/table';
import SocialShare from './modules/social-share';
import Tabs from './modules/tabs';

const modules: any = {
	'theme-selection': ThemeSelection,
	'primary-nav': PrimaryNav,
	'header-search': HeaderSearch,
	'header-profile': HeaderProfile,
	'header-menu': HeaderMenu,
	'oembed': OEmbed,
	'tabs': Tabs,
	'skiplink': Skiplink,
	'table': Table,
	'social-share': SocialShare,
	'display-options': DisplayOptions,
};

Array.from( document.querySelectorAll<HTMLElement>( '[data-module]' ) ).forEach( ( element: HTMLElement ) => {
	if ( element === undefined ) {
		return;
	}

	const name = element.dataset.module;

	if ( ! name ) {
		return;
	}

	const module = modules[ name ];

	if ( module ) {
		new module( element );
		element.setAttribute( 'data-initialized', 'true' );
	}
} );

console.log( '%c  Powered by level-level.com 💪  ', 'background: #222; color: #fff; font-size: 14px; font-weight: normal; border-radius: 5px; line-height: 30px;' ); //eslint-disable-line no-console
