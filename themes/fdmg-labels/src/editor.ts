import './styles/editor.scss';

import './styles/layout/section.css';
import './styles/layout/container.css';
import './styles/components/navigation/widget-nav.css';

// Global variable provided by WordPress
declare const wp: any; // eslint-disable-line
declare const lodash: any; // eslint-disable-line

/**
 * Disable default WordPress fullscreen mode
 */
const isFullscreenMode = wp.data.select('core/edit-post').isFeatureActive('fullscreenMode');
if (isFullscreenMode) {
	wp.data.dispatch('core/edit-post').toggleFeature('fullscreenMode');
}

type Block = {
	[key: string]: string;
};

wp.domReady(() => {
	// Disable default WordPress style for core block(s)
	wp.blocks.unregisterBlockStyle('core/table', 'stripes');
	wp.blocks.unregisterBlockStyle('core/image', 'rounded');

	// Define specific Gutenberg embeds blocks
	const enabledEmbeds = ['youtube', 'twitter', 'vimeo'];
	const embedBlock = wp.blocks.getBlockVariations('core/embed');

	if (embedBlock) {
		embedBlock.forEach((block: Block) => {
			if (!enabledEmbeds.includes(block.name)) {
				wp.blocks.unregisterBlockVariation('core/embed', block.name);
			}
		});
	}
});

// Disable default options for following core blocks
const coreBlocks = ['core/heading'];

wp.hooks.addFilter('blocks.registerBlockType', 'fdmg-labels/blocks', function (settings: any, name: string) {
	if (coreBlocks.includes(name)) {
		return lodash.assign({}, settings, {
			supports: lodash.assign({}, settings.supports, {
				align: false,
				anchor: false,
				className: false,
				customClassName: false,
				color: false,
				filter: false,
			}),
		});
	}

	if (name === 'core/image') {
		return lodash.assign({}, settings, {
			supports: lodash.assign({}, settings.supports, {
				color: false,
				filter: false,
			}),
		});
	}

	if (name === 'core/embed') {
		return lodash.assign({}, settings, {
			supports: lodash.assign({}, settings.supports, {
				align: false,
			}),
		});
	}

	return settings;
});
