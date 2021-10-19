export default {
	title: 'Components/SocialShareButton',
	parameters: {
		server: {
			id: 'styleguide/components/social-share-button.twig',
		},
	},
	argTypes: {
		post_title: {
			control: 'text'
		},
		show_button_title: {
			control: 'boolean'
		},
	},
	args: {
		post_title: 'Example post title',
		url: '#',
		show_button_title: true,
	},
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
