export default {
	title: 'Components/Teasers/Download',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-download.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
		filetype: {
			control: 'text',
		},
		filesize: {
			control: 'text',
		},
	},
	args: {
		title: 'Example download',
		filetype: 'PDF',
		filesize: '2MB',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
