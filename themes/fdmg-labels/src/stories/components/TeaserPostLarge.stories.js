import Terms from '../data/Terms.js';
import Authors from '../data/Authors.js';

export default {
	title: 'Components/Teasers/Post/Large',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-post-large.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
		excerpt: {
			control: 'text',
		},
		categories: {
			control: 'array',
		},
		authors: {
			control: 'array',
		},
		date: {
			control: 'array',
		},
		comments: {
			control: 'number',
		},
		show_paywall_label: {
			control: 'boolean',
		},
	},
	args: {
		image: '<img src="https://place-hold.it/1024x578" loading="lazy" width="1024" height="578" alt="">',
		title: 'Example Post teaser',
		url: '#',
		excerpt:
			'<p>Nesciunt eligendi sociis metus penatibus velit accumsan! Corrupti convallis nisl? Inventore, autem urna elit nibh, ultricies illo sodales vero massa.</p>',
		categories: Terms,
		date: {
			date: '21 Apr, 2021',
			datetime: '2021-04-21',
			date_full: '21 april 2021',
			readable: '20 hours ago',
		},
		authors: Authors,
		comments: 2,
		show_paywall_label: false,
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
