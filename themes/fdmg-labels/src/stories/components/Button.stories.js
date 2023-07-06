import Icon from '../controls/Icon';

export default {
	title: 'Components/Button',
	parameters: {
		server: {
			id: 'styleguide/components/buttons/button-link.twig',
		},
	},
	argTypes: {
		text: {
			control: 'text',
		},
		icon: Icon,
		icon_left: {
			name: 'icon left',
			control: 'boolean',
		},
		highlight_icon: {
			name: 'highlight icon',
			control: 'boolean',
		},
	},
	args: {
		url: '#',
		text: 'Button',
		icon_left: false,
		highlight_icon: false,
	},
};

const Template = () => {};

export const Primary = Template.bind({});
Primary.args = {
	class: 'btn--primary',
	text: 'Primary button',
};
Primary.argTypes = {
	class: {
		options: [
			Primary.args.class,
			`${Primary.args.class} btn--full`,
			`${Primary.args.class} btn--no-padding`,
		],
		control: {
			type: 'select',
		},
	},
};

export const Secondary = Template.bind({});
Secondary.args = {
	class: 'btn--secondary',
	text: 'Secondary button',
};
Secondary.argTypes = {
	class: {
		options: [
			Secondary.args.class,
			`${Secondary.args.class} btn--full`,
			`${Secondary.args.class} btn--no-padding`,
		],
		control: {
			type: 'select',
		},
	},
};

export const Tertiary = Template.bind({});
Tertiary.args = {
	class: 'btn--tertiary',
	text: 'Tertiary button',
	icon_left: true,
	highlight_icon: true,
	icon: 'arrow-right',
};
Tertiary.argTypes = {
	class: {
		options: [Tertiary.args.class, `${Tertiary.args.class} btn--full`],
		control: {
			type: 'select',
		},
	},
};

export const Link = Template.bind({});
Link.args = {
	class: 'btn--link',
	text: 'Link button',
};
Link.argTypes = {
	class: {
		options: [
			Link.args.class,
			`${Link.args.class} btn--full`,
			`${Link.args.class} btn--no-padding`,
		],
		control: {
			type: 'select',
		},
	},
};
