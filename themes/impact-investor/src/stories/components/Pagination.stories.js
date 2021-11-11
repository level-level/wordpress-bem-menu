export default {
	title: 'Components/Pagination',
	parameters: {
		server: {
			id: 'partials/components/pagination.twig',
		},
	},
	argTypes: {
		test_args: {
			control: 'object'
		},
	},
	args: {
		test_args: {
			current: 2,
			total: 5,
		},
	},
};

const Template = () => {};

export const Default = Template.bind( {} );

export const ManyPagesStart = Template.bind( {} );
ManyPagesStart.args = {
	test_args: {
		current: 1,
		total: 123,
	},
};

export const ManyPagesMiddle = Template.bind( {} );
ManyPagesMiddle.args = {
	test_args: {
		current: 142,
		total: 276,
	},
};

export const ManyPagesEnd = Template.bind( {} );
ManyPagesEnd.args = {
	test_args: {
		current: 435,
		total: 435,
	},
};

export const FewPages = Template.bind( {} );
FewPages.args = {
	test_args: {
		current: 2,
		total: 3,
	},
};
