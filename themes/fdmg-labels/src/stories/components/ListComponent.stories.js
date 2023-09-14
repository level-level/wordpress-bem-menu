import Posts from '../data/Posts';
import Comments from '../data/Comments';

export default {
	title: 'Components/ListComponent',
	parameters: {
		server: {
			id: 'styleguide/components/list-component.twig',
		},
	},
	argTypes: {
		heading: {
			control: 'text',
		},
		objects: {
			control: 'array',
		},
		button_text: {
			control: 'text',
		},
		button_link: {
			control: 'text',
		},
		style: {
			options: ['default', 'dark', 'light'],
			control: {
				type: 'select',
			},
		},
	},
	args: {
		heading: 'Latest news',
		objects: Posts,
		button_text: 'More articles',
		button_link: '#',
		style: '',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};

export const LatestComments = Template.bind({});
LatestComments.args = {
	heading: 'Latest comments',
	objects: Comments,
};

LatestComments.argTypes = {
	comment_content: {
		control: 'text',
	},
	comment_author: {
		control: 'text',
	},
	button_text: '',
	button_link: '',
	style: 'light',
};
