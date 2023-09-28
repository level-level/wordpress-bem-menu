export default {
	title: 'Components/Social/SocialShare',
	parameters: {
		server: {
			id: 'styleguide/components/social-share.twig',
		},
	},
	argTypes: {
		socials: {
			control: 'array',
		},
	},
	args: {
		socials: [
			{
				title: 'Facebook',
				type: 'facebook',
			},
			{
				title: 'X',
				type: 'x',
			},
			{
				title: 'LinkedIn',
				type: 'linkedin',
			},
			{
				title: 'E-mail',
				type: 'email',
			},
		],
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
