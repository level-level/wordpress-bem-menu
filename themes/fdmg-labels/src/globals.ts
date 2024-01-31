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
	}
}
