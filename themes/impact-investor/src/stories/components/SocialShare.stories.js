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
		}
	},
	args: {
		socials: [
			{
				title: 'Facebook',
				type: 'facebook',
			},
			{
				title: 'Twitter',
				type: 'twitter',
			},
			{
				title: 'Google Plus',
				type: 'googleplus',
			},
			{
				title: 'LinkedIn',
				type: 'linkedin',
			},
			{
				title: 'E-mail',
				type: 'email',
			},
			{
				title: 'WhatsApp',
				type: 'whatsapp',
			},
		]
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
