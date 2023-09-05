<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Partner;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;
use Gutenberg\Blocks\base_block;

class Partners {
	public const BLOCK_NAME = 'partners';

	protected const FIELD_GROUP = 'block_partners';

	public const BLOCK_META_TITLE    = 'title';
	public const BLOCK_META_PARTNERS = 'partners';

	public function register_hooks(): void {
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_PARTNER_POST_TYPE ) ) {
			add_action( 'acf/init', array( $this, 'register' ) );
			add_action( 'acf/init', array( $this, 'register_fields' ) );
		}
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Partners', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of partners.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'image-filter',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'partners', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode' => base_block::PREVIEW_MODE_DATA,
						),
					),
				),
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Partners', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: \'Our partners\'', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
					Relationship::make( _x( 'Select the partners', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_PARTNERS )
						->postTypes( array( Partner::POST_TYPE_NAME ) )
						->filters(
							array(
								'search',
							)
						)
						->returnFormat( 'object' )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}
}
