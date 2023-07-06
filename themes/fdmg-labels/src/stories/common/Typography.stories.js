export default {
	title: 'Common/Typography',
	parameters: {
		server: {
			id: 'styleguide/common/typography/headings.twig',
		},
	},
	argTypes: {
		content: {
			control: 'text',
		},
	},
	args: {
		content: 'Almost before we knew it, we had left the ground.',
	},
};

const Template = () => {};

export const Headings = Template.bind({});
Headings.args = {};

export const Paragraphs = Template.bind({});
Paragraphs.args = {
	content:
		'On July 16, 1969, the <strong>Apollo 11</strong> spacecraft launched from the <a href="#">Kennedy Space Center</a> in Florida. Its mission was to go where no human being had gone before—the moon! The crew consisted of <strong>Neil Armstrong</strong>, <strong>Michael Collins</strong>, and <strong>Buzz Aldrin</strong>. The spacecraft landed on the moon in the <a href=""><strong>Sea of Tranquility</strong></a>, a basaltic flood plain, on July 20, 1969. The moonwalk took place the following day. On July 21, 1969, at precisely 10:56 EDT, Commander Neil Armstrong emerged from the Lunar Module and took <em>his famous first step onto the moon’s surface</em>. He declared, “That’s one small step for man, one giant leap for mankind.” It was a monumental moment in human history!',
};

Paragraphs.parameters = {
	server: {
		id: 'styleguide/common/typography/paragraphs.twig',
	},
};

export const Blockquote = Template.bind({});
Blockquote.args = {
	content:
		"If you set your goals ridiculously high and it's a failure, you will fail above everyone else's success",
};

Blockquote.parameters = {
	server: {
		id: 'styleguide/common/typography/blockquote.twig',
	},
};

export const List = Template.bind({});
List.args = {
	content: 'A list item',
};

List.parameters = {
	server: {
		id: 'styleguide/common/typography/list.twig',
	},
};
