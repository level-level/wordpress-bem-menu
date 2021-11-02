import Posts from '../data/Posts';

export default {
	title: 'Components/LatestArticles',
	parameters: {
		server: {
			id: 'styleguide/components/latest-articles.twig',
		},
	},
	argTypes: {
		heading: {
			control: 'text'
		},
		objects: {
			control: 'array'
		},
		button_text: {
			control: 'text'
		},
		button_link: {
			control: 'text'
		},
		style: {
			options: [
				'default',
				'dark',
			],
			control: {
				type: 'select',
			}
		},
	},
	args: {
		heading: 'Latest news',
		objects: Posts,
		button_text: 'More articles',
		button_link: '#',
		style: '',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
