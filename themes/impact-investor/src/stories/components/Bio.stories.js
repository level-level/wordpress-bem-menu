export default {
	title: 'Components/Bio',
	parameters: {
		server: {
			id: 'styleguide/components/bio.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		subtitle: {
			control: 'text'
		},
		excerpt: {
			control: 'text'
		},
		email: {
			control: 'text'
		},
		linkedin: {
			control: 'text'
		},
		image: {
			control: 'text'
		},
		logo: {
			control: 'text'
		},
	},
	args: {
		title: 'Kevinio Level',
		subtitle: '',
		excerpt: '<p>Kevin Level coordinates the background articles for Imapct investor. He previously worked at FD, de Volkskrant and Consumer Money Guide, among others.</p>',
		image: '<img src="https://place-hold.it/115x115" loading="lazy" width="115" height="115" alt="">',
		logo: '',
		email: 'email@email.com',
		linkedin: 'https://linkedin.com/',
	}
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
