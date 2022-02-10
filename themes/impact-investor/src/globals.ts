/**
 * Data is defined in SVGIcons.php via wp_localize_script()
 */
export type Icons = {
	arrowDown: string;
}

/**
 * Data is defined in ScriptTranslations.php via wp_localize_script()
 */
export type I18n = {
	navigation: {
		subMenu: string;
	};
	socialShare: string;
}

/**
 * Interface for all modules used by the data attribute: data-module
 */
export interface IModule {
	element: HTMLElement;
	initialize(): void;
}
