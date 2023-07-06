import Terms from '../data/Terms.js';

export default {
	title: 'Components/Teasers/Post/Inline',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-post-inline.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text',
		},
		terms: {
			control: 'array',
		},
	},
	args: {
		image: '<img src="https://place-hold.it/300x195" loading="lazy" width="300" height="195" alt="">',
		image_square: '<img src="https://place-hold.it/115x115" loading="lazy" width="115" height="115" alt="">',
		title: 'CDC Climate Strategy director: Focus on nature to build climate resilience',
		url: '#',
		terms: Terms,
	},
};

const Template = () => {};

export const Default = Template.bind({});
Default.args = {};
