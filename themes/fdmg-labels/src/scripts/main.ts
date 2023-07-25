import ModuleInitializer from './utilities/module-initializer';
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

ModuleInitializer({
	'theme-selection': ThemeSelection,
	'primary-nav': PrimaryNav,
	'header-search': HeaderSearch,
	'header-profile': HeaderProfile,
	'header-menu': HeaderMenu,
	oembed: OEmbed,
	tabs: Tabs,
	skiplink: Skiplink,
	table: Table,
	'social-share': SocialShare,
	'display-options': DisplayOptions,
});

/**
 * Reinitialize modules after the paywall is removed
 */
document.addEventListener('onLLPaywallRemoved', () => {
	ModuleInitializer({
		oembed: OEmbed,
		table: Table,
		'social-share': SocialShare,
	});
});

// eslint-disable-next-line
console.log(
	'%c  Powered by level-level.com 💪  ',
	'background: #222; color: #fff; font-size: 14px; font-weight: normal; border-radius: 5px; line-height: 30px;',
);
