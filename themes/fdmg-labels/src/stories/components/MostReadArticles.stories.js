import Posts from '../data/Posts';

export default {
	title: 'Components/MostReadArticles',
	parameters: {
		server: {
			id: 'styleguide/components/most-read-articles.twig',
		},
	},
	argTypes: {
		objects: {
			control: 'array'
		},
	},
	args: {
		objects: Posts,
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
