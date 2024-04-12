import { DataLayerConfig } from '../../globals';

const pushToDataLayer = (config: DataLayerConfig) => {
	window.dataLayer.push({
		event: 'article_impression',
		event_info: {
			category: 'user interaction',
			action: 'view item list',
			label: `${config.id}`,
			value: null,
			event_context: {
				component: 'article',
				component_subtype: `${config.component_subtype}`,
				section_title: `${config.categories}`,
			},
			ecommerce: {
				article: [
					{
						article_id: `${config.id}`,
						article_name: `${config.title}`,
						article_category: `${config.categories}`,
						article_type: 'article',
						author_name: `${config.author}`,
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

const handleArticleImpression = (config: DataLayerConfig) => {
	const { element } = config;

	const articles = document.querySelectorAll(`[data-teaser-post`);
	const articlesArray = Array.from(articles);
	config.article_index = ( articlesArray.indexOf(element) + 1 );

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting && entry.intersectionRatio === 1) {
					pushToDataLayer(config);
					observer.disconnect();
				}
			});
		},
		{ threshold: 1 },
	);

	observer.observe(element);
};

export default handleArticleImpression;
