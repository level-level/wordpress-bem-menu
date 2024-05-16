/**
 * Data is defined in ScriptTranslations.php via wp_localize_script()
 */
export type I18n = {
	socialShare: string;
	invalidDateInput: string;
	formError: string;
	loggedIn: string;
	copied: string;
};

export type Auth = {
	loginUrl: string;
}

export type ShortInit = {
	url: string;
}

/**
 * Interface for all modules used by the data attribute: data-module
 */
export interface IModule {
	element: HTMLElement;
	initialize(): void;
}

declare global {
	interface Window {
		sentry?: {
			dsn: string;
			environment: string;
			release: string;
			trace_sample_rate: string;
			error_sample_rate: string;
			transaction_data: any;
		};
		dataLayer: any[];
		__tcfapi: any;
	}
}

export type RestApi = {
	root: string;
};

/**
 * Interface for dataLayer configs
 */
export interface DataLayerConfig {
	element: HTMLElement;
	event: string;
	id?: string;
	title?: string;
	url?: string;
	author?: string;
	type?: string;
	publication_date?: string;
	publication_time?: string;
	revisions_count?: string;
	article_index?: number;
	component_subtype?: string;
	parent_component?: string;
	categories?: string;
	share_type?: string;
	page_category?: string;
	page_type?: string;
	page_subtype?: string;
	do_not_track?: boolean;
}
