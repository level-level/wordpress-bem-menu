export default {
	title: 'Components/Teasers/Partner',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-partner.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
		excerpt: {
			control: 'text',
		},
	},
	args: {
		image: '<img src="https://place-hold.it/300x100" loading="lazy" width="300" height="100" alt="">',
		title: 'Actiam',
		url: '#',
		excerpt: '<p>ACTIAM is a trendsetter when it comes to impact investing. We launched our first institutional funds in microfinancing as early as 2007 and 2008: ACTIAM Institutional Microfinance Fund I and II respectively. Our strength is making investment opportunities scalable in high-impact themes. With a track record of over almost 15 years in impact investing, our team demonstrates a sound performance.</p>',
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
