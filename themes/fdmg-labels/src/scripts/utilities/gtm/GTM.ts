import ISubmissionData from './ISubmissionData';

window.__tcfapi = window.__tcfapi || undefined;

declare global {
	interface Window {
		dataLayer: any[];
	}
	interface Window {
		__tcfapi: any;
	}
}

class GTM {
	trackFormConfirmationFromUrl(): void {
		const submissionData = this.getSubmissionDataFromUrl();
		if (!submissionData) {
			return;
		}

		window.trackFormConfirmationGTM(submissionData);
	}

	getSubmissionDataFromUrl(): ISubmissionData | null {
		const urlSearchParams = new URLSearchParams(window.location.search);
		const urlParameterValue = urlSearchParams.get('fdmg-gf-data');

		if (!urlParameterValue) {
			return null;
		}

		let jsonObject = null;
		try {
			const jsonUrlParameterValue = window.atob(urlParameterValue);
			jsonObject = JSON.parse(jsonUrlParameterValue);
		} catch (e) {
			return null;
		}

		if (typeof jsonObject !== 'object') {
			return null;
		}

		return <ISubmissionData>jsonObject;
	}

	trackFormConfirmation(data: ISubmissionData): void {
		if(data.form_type === 'newsletter') {
			if ( typeof window.__tcfapi !== 'undefined' ) {
				this.initSourcepoint ( 'newsletter', data );
			} else {
				this.trackNewsletterSubscription(data);
			}
		}

		if(data.form_type === 'download') {
			if ( typeof window.__tcfapi !== 'undefined' ) {
				this.initSourcepoint ( 'download', data );
			} else {
				this.trackDownload(data);
			}
		}
	}

	/**
	 * Use the Sourcepoint (tcfapi) to handle user consent
	 */
	initSourcepoint(type: string, data: ISubmissionData) {
		window.__tcfapi(
			'addEventListener',
			2,
			(tcData: { eventStatus: string }) => {
				if (
					tcData.eventStatus === 'tcloaded' ||
					tcData.eventStatus === 'useractioncomplete'
				) {
					if (type === 'newsletter') {
						this.trackNewsletterSubscription(data);
					}
					if (type === 'download') {
						this.trackDownload(data);
					}
				}
			},
		);
	}

	// track newsletter subscription
	trackNewsletterSubscription(data: ISubmissionData): void {
		window.dataLayer = window.dataLayer || [];
		window.dataLayer.push({
			event: 'form_submit',
			event_info: {
				category: 'forms',
				action: 'subscribe',
				label: 'newsletter',
				value: null,
			},
			event_context: {
				component: 'form',
				sub_component: 'newsletter',
				index: null,
				section_title: data.form_title ?? null,
				click_text: data.click_text ?? null,
				page_url: window.location.href ?? null,
				referrer: null,
			}
		});
	}

	// track download
	trackDownload(data: ISubmissionData): void {
		window.dataLayer = window.dataLayer || [];

		const categories = data.post_categories ? data.post_categories.join(', ') : '';

		window.dataLayer.push({
			event: 'article_download',
			event_info: {
				category: 'downloads',
				action: 'article_download',
				label: data.post_id,
				value: null,
			},
			event_context: {
				component: 'report',
				sub_component: 'main article',
				section_title: data.form_title,
			},
			ecommerce: {
				article: [{
					article_id: data.post_id,
					article_name: data.post_title,
					article_type: null,
					article_category: categories ?? null,
					author_name: data.post_author ?? null,
					article_publication_date: data.post_publication_date ?? null,
					article_publication_time: data.post_publication_time ?? null,
					article_version: data.post_version ?? null,
					article_index: null,
				}]
			}
		});
	}
}

export default GTM;
