import { IModule, RestApi } from '../../globals';

declare const restApi: RestApi;

type FragmentApi = {
	comment_form: string;
};

class CommentForm implements IModule {
	element: HTMLElement;
	config: {
		post_id: number | null;
	};

	constructor(element: HTMLElement) {
		this.element = element;
		this.config = this.element.dataset.config ? JSON.parse(this.element.dataset.config) : {};
		this.initialize();
	}

	initialize() {
		this.addEventListeners();
	}

	addEventListeners() {
		if (!this.config.post_id) {
			return;
		}

		this.fetchCommentForm(this.config.post_id).then((data) => {
			if (data?.comment_form) {
				this.element.innerHTML = data.comment_form;
			}
		});
	}

	async fetchCommentForm(post_id: number) {
		try {
			const response = await fetch(`${restApi.root}fdmg-labels/v1/page-fragments/${post_id}`);

			if (!response.ok) {
				throw new Error(`Error fetching comment form for post ${post_id}`);
			}

			const data: FragmentApi = await response.json();
			return data;
		} catch (error) {
			console.error(error); // eslint-disable-line
		}
	}
}

export default CommentForm;
