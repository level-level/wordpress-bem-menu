import ModuleInitializer from './utilities/module-initializer';
import ThemeSelection from './modules/theme-selection';
import PrimaryNav from './modules/primary-nav';
import HeaderMenu from './modules/header-menu';
import HeaderSearch from './modules/header-search';
import HeaderProfile from './modules/header-profile';
import DisplayOptions from './modules/display-options';
import OEmbed from './modules/oembed';
import Table from './modules/table';
import SocialShare from './modules/social-share';
import Tabs from './modules/tabs';
import StickyAdvertisement from './modules/sticky-advertisement';
import NavigationAdvertisement from './modules/navigation-advertisement';
import FilterModal from './modules/filter-modal';
import FilterForm from './modules/filter-form';
import DateRangeValidator from './modules/date-range-validator';
import ToggleListItems from './modules/toggle-list-items';
import LoginButton from './modules/login-button';
import TextZoom from './modules/text-zoom';
import TagManager from './modules/tag-manager';
import PaywallLoginButton from './modules/paywall-login-button';
import Cookies from 'js-cookie';
import User from './model/user';
import { Auth } from '../globals';

ModuleInitializer({
	'navigation-advertisement': NavigationAdvertisement,
	'theme-selection': ThemeSelection,
	'primary-nav': PrimaryNav,
	'header-search': HeaderSearch,
	'header-profile': HeaderProfile,
	'header-menu': HeaderMenu,
	oembed: OEmbed,
	tabs: Tabs,
	table: Table,
	'social-share': SocialShare,
	'display-options': DisplayOptions,
	'sticky-advertisement': StickyAdvertisement,
	'filter-modal': FilterModal,
	'filter-form': FilterForm,
	'date-range-validator': DateRangeValidator,
	'toggle-list-items': ToggleListItems,
	'login-button': LoginButton,
	'text-zoom': TextZoom,
	'tag-manager': TagManager,
	'paywall-login-button': PaywallLoginButton,
});

/**
 * Event when the paywall is removed
 */
document.addEventListener('onLLPaywallRemoved', () => {
	// Remove the paywall a11y description
	document.querySelectorAll('.js-paywall-a11y-description').forEach((element) => element.remove());

	// Reinitialize modules after the paywall is removed
	ModuleInitializer({
		oembed: OEmbed,
		table: Table,
		'social-share': SocialShare,
	});
});

// eslint-disable-next-line
console.log('%c  Powered by level-level.com 💪  ', 'background: #222; color: #fff; font-size: 14px; font-weight: normal; border-radius: 5px; line-height: 30px;');
