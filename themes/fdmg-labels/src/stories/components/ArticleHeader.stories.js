import Terms from '../data/Terms.js';
import Authors from '../data/Authors.js';

export default {
	title: 'Components/ArticleHeader',
	parameters: {
		server: {
			id: 'styleguide/components/article-header.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		terms: {
			control: 'array'
		},
		authors: {
			control: 'array'
		},
		label: {
			control: 'text'
		},
	},
	args: {
		title: 'Borski: Female venture capitalists are key to female innovation',
		terms: Terms,
		date: 'Apr 21, 2021',
		datetime: '2021-04-21',
		authors: Authors,
		label: '',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
