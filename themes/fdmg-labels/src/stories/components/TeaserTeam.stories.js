export default {
	title: 'Components/Teasers/Team',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-team.twig',
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
		image: '<img src="https://place-hold.it/115x115" loading="lazy" width="115" height="115" alt="">',
		title: 'Kevinio Level',
		url: '#',
		excerpt: '<p>Kevin Level Kevin Level coordinates the background articles for Imapct investor. He previously worked at FD, de Volkskrant and Consumer Money Guide, among others.</p>',
	},
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
