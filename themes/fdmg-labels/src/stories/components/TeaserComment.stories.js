export default {
	title: 'Components/Teasers/Comment',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-comment.twig',
		},
	},
	argTypes: {
		author: {
			control: 'text',
		},
		time: {
			control: 'text',
		},
		content: {
			control: 'text',
		},
	},
	args: {
		author: 'Kevinio',
		time: '20 minutes ago',
		content: 'Nesciunt eligendi sociis metus penatibus velit accumsan! Corrupti convallis nisl? Inventore, autem urna elit nibh, ultricies illo sodales vero massa.',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
