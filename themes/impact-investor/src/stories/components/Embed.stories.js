export default {
	title: 'Components/Embed',
	parameters: {
		server: {
			id: 'partials/components/oembeds/oembed.twig',
		},
	},
	argTypes: {
		notice_text: {
			name: 'Notice text',
			control: 'text'
		},
		button_text: {
			name: 'Button text',
			control: 'text'
		},
	},
	args: {
		notice_text: 'Cookie notice text',
		button_text: 'Button text',
		thumbnail: 'https://place-hold.it/1280x720',
		html: '',
	}
};

const Template = () => {};

export const CookieNotice = Template.bind( {} );
CookieNotice.args = {
	type: '',
};

export const Video = Template.bind( {} );
Video.args = {
	type: 'video',
};

Video.parameters = {
	server: {
		id: 'partials/components/oembeds/oembed-video.twig',
	}
};

export const Audio = Template.bind( {} );
Audio.args = {
	type: 'audio',
};

Audio.parameters = {
	server: {
		id: 'partials/components/oembeds/oembed-audio.twig',
	}
};
