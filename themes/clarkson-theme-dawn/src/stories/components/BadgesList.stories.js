import Terms from '../data/Terms.js';

export default {
	title: 'Components/Badges/BadgesList',
	parameters: {
		server: {
			id: 'styleguide/components/badges/list-badges.twig',
		},
	},
	argTypes: {
		badges: {
			control: 'array'
		},
	},
	args: {
		badges: Terms
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
