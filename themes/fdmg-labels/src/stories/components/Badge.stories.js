export default {
	title: 'Components/Badges/Badge',
	parameters: {
		server: {
			id: 'styleguide/components/badges/badge.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
		url: {
			control: 'text',
		},
	},
	args: {
		title: 'Interview',
		url: '#remove-me-to-see-default-badge',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
