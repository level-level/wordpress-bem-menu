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
				title: 'Facebook',
				url: '#',
				icon: 'facebook',
			},
			{
				title: 'Twitter',
				url: '#',
				icon: 'twitter',
			},
			{
				title: 'LinkedIn',
				url: '#',
				icon: 'linkedin',
			},
			{
				title: 'YouTube',
				url: '#',
				icon: 'youtube',
			},
		],
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
