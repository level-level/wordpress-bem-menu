export default interface ISubmissionData {
	form_id: number;
	form_type: string;
	form_title: string;
	click_text: string;
	post_id: number;
	post_url: string;
	post_title: string;
	post_categories: Array<string>;
	post_publication_date: string;
	post_publication_time: string;
	post_version: string;
	post_author: string;
	email_address: string;
	entry_id: number;
}
