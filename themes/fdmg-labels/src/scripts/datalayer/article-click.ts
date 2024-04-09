import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
	window.dataLayer.push({
		event: 'article_click',
		event_info: {
			category: 'user interactions',
			action: 'article click',
			label: `${config.id}`,
			value: null,
			event_context: {
				component: 'article',
				component_subtype: `${config.component_subtype}`,
			},
			ecommerce: {
				article: [
					{
						article_id: `${config.id}`,
						article_name: `${config.title}`,
						article_category: `${config.categories}`,
						article_type: 'article',
						author_name: `${config.author}`,
						article_label: `${config.id}`,
						article_publication_date: `${config.publication_date}`,
						article_publication_time: `${config.publication_time}`,
						article_version: `${config.revisions_count}`,
						article_index: `${config.article_index}`,
					},
				],
			},
		},
	});
}

const handleArticleClick = (config: DataLayerConfig) => {
	const { element } = config;
	const links = element.querySelectorAll('a');

	const articles = document.querySelectorAll(`[data-teaser-post`);
	const articlesArray = Array.from(articles);
	config.article_index = ( articlesArray.indexOf(element) + 1 );

	links.forEach((link) => {
		link.addEventListener('click', () => {
			pushToDataLayer(config);
		});
	});
};

export default handleArticleClick;
