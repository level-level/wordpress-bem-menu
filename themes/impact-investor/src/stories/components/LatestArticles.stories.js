import Posts from '../data/Posts';

export default {
	title: 'Components/LatestArticles',
	parameters: {
		server: {
			id: 'styleguide/components/latest-articles.twig',
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
