import Terms from '../data/Terms.js';
import Authors from '../data/Authors.js';

export default {
	title: 'Components/Teasers/Post/Wide',
	parameters: {
		server: {
			id: 'styleguide/components/teasers/teaser-post-wide.twig',
		},
	},
	argTypes: {
		title: {
			control: 'text'
		},
		excerpt: {
			control: 'text'
		},
		terms: {
			control: 'array'
		},
		authors: {
			control: 'array'
		},
		date: {
			control: 'text'
		},
		comments: {
			control: 'number'
		}
	},
	args: {
		image: '<img src="https://place-hold.it/1048x680" loading="lazy" class="teaser-post__image" width="1048" height="680" alt="">',
		image_square: '<img src="https://place-hold.it/115x115" loading="lazy" class="teaser-post__image" width="115" height="115" alt="">',
		title: 'Example Post teaser',
		url: '#',
		excerpt: '<p>Nesciunt eligendi sociis metus penatibus velit accumsan! Corrupti convallis nisl? Inventore, autem urna elit nibh, ultricies illo sodales vero massa.</p>',
		terms: Terms,
		date: 'Apr 21, 2021',
		datetime: '2021-04-21',
		authors: Authors,
		comments: 2,
	},
};

const Template = () => {};

export const Default = Template.bind( {} );
Default.args = {};
