import IconsList from '../data/Icons';

export default {
	title: 'Common/Icons',
	parameters: {
		server: {
			id: 'styleguide/common/icons.twig',
		},
	},
	argTypes: {

	},
	args: {
		icons: IconsList,
	}
};

const Template = () => {};

export const Icons = Template.bind( {} );
Icons.args = {};
