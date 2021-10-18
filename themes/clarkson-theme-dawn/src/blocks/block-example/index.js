const { __ } = wp.i18n;
const { PostFeaturedImage } = wp.editor;
const { registerBlockType } = wp.blocks;

export default registerBlockType(
	'll/block-example',
	{
		title: __( 'Uitgelichte afbeelding', 'clarkson-theme-dawn' ),
		icon: 'format-image',
		category: 'level-level',
		keywords: [
			__( 'Uitgelichte afbeelding', 'clarkson-theme-dawn' ),
		],
		supports: {
			align: ['center', 'wide'],
		},
		edit( { className } ) {
			return (
				<div className={ className }>
					<PostFeaturedImage />
				</div>
			);
		},
		save() {
			return null;
		},
	}
);
