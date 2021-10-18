import './blocks/blocks.js';
import './styles/editor.scss';

/**
 * Disable default WordPress fullscreen mode
 */
const isFullscreenMode = wp.data.select( 'core/edit-post' ).isFeatureActive( 'fullscreenMode' );
if ( isFullscreenMode ) {
	wp.data.dispatch( 'core/edit-post' ).toggleFeature( 'fullscreenMode' );
}

wp.domReady( () => {

	// Disable default WordPress style for core block(s)
	wp.blocks.unregisterBlockStyle( 'core/table', 'stripes' );

	// Define specific Gutenberg embeds blocks
	const enabledEmbeds = [ 'youtube', 'twitter', 'vimeo', 'spotify', 'soundcloud' ];
	const embedBlock = wp.blocks.getBlockVariations( 'core/embed' );

	if ( embedBlock ) {
		embedBlock.forEach( ( el ) => {
			if ( !enabledEmbeds.includes( el.name ) ) {
				wp.blocks.unregisterBlockVariation( 'core/embed', el.name );
			}
		} );
	}
} );
