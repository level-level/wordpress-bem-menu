import ISubmissionData from './ISubmissionData';

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
		const urlParameterValue = urlSearchParams.get('gf-data');
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
		console.log('trackFormConfirmation', data);

		// check for consent here

		window.dataLayer = window.dataLayer || [];

		if(data.form_type === 'newsletter') {
			this.trackNewsletterSubscription(data);
		}

		if(data.form_type === 'download') {
			this.trackDownload(data);
		}
	}

	// track newsletter subscription
	trackNewsletterSubscription(data: ISubmissionData): void {
		window.dataLayer.push({
			event: 'newsletter_subscription',
		});
	}

	// track download
	trackDownload(data: ISubmissionData): void {
		window.dataLayer.push({
			event: 'download',
		});
	}
}

export default GTM;

/**
 * Track a form submission.
 * Make available globally on window for use in HTML directly.
 */
window.trackFormConfirmationGTM = (data: ISubmissionData): void => {
	new GTM().trackFormConfirmation(data);
};
