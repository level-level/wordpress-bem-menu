import Align from "../controls/Align";

export default {
	title: 'Components/CTA',
	parameters: {
		server: {
			id: 'styleguide/components/cta/cta.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		text: {
			control: 'text',
		},
		button: {
			control: 'object'
		},
		align: Align,
	},
	args: {
		text: '<p>Mollit phasellus iste sapien rutrum laboris, repudiandae irure quod mauris hac harum nesciunt quas sollicitudin voluptatem volutpat laboris, facilisi lacinia pede? Bibendum non eius! Nisi maiores tempus? Pariatur?</p>',
		button: {
			url: '#',
			title: 'Button text',
		},
		align: 'wide',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {
	title: 'Default CTA',
};
