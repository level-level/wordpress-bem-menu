export default {
	title: 'Components/Badges/Badge',
	parameters: {
		server: {
			id: 'styleguide/components/badges/badge.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
	},
	args: {
		title: 'Interview',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
