export default {
	title: 'Components/AuthorHeader',
	parameters: {
		server: {
			id: 'styleguide/components/author-header.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		subtitle: {
			control: 'text'
		}
	},
	args: {
		title: 'Kevinio Level',
		subtitle: 'Author',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
