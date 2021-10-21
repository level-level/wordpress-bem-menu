import Icon from "../controls/Icon";

export default {
	title: 'Components/Button',
	parameters: {
		server: {
			id: 'styleguide/components/buttons/button-link.twig',
		},
	},
	argTypes: {
		text: {
			control: 'text'
		},
		icon: Icon,
		icon_left: {
			name: 'icon left',
			control: 'boolean'
		},
	},
	args: {
		url: '#',
		text: 'Button',
		icon_left: false,
	}
};

const Template = () => {};

export const Primary = Template.bind( {} );
Primary.args = {
	class: 'btn--primary',
};

export const Link = Template.bind( {} );
Link.args = {
	class: 'btn--link',
};
