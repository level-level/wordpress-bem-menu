export default {
	title: 'Components/Social/SocialMediaLinks',
	parameters: {
		server: {
			id: 'styleguide/components/social-media-links.twig',
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
				type: {
					label: 'Facebook',
					value: 'facebook',
				},
				url: '#',
			},
			{
				type: {
					label: 'X',
					value: 'x',
				},
				url: '#',
			},
			{
				type: {
					label: 'LinkedIn',
					value: 'linkedin',
				},
				url: '#',
			},
			{
				type: {
					label: 'YouTube',
					value: 'youtube',
				},
				url: '#',
			},
		],
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
