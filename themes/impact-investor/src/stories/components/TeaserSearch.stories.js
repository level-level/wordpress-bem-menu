export default {
	title: 'Components/Teasers/Search',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-search.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		excerpt: {
			control: 'text'
		},
	},
	args: {
		title: 'Example search teaser',
		url: '#',
		excerpt: '<p>Nesciunt eligendi sociis metus penatibus velit accumsan! Corrupti convallis nisl? Inventore, autem urna elit nibh, ultricies illo sodales vero massa.</p>',
	},
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
