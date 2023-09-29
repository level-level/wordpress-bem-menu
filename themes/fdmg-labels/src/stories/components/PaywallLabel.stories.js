export default {
	title: 'Components/Teasers/Post/PaywallLabel',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/post/paywall-label.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
	},
	args: {
		title: 'Free article',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
