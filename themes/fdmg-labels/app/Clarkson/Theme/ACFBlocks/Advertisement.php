<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Advertisement {
	public const BLOCK_NAME = 'advertisement';

	protected const FIELD_GROUP = 'block_advertisement';

	public const BLOCK_META_ADVERTISEMENT_SLOT = 'advertisement_slot';
	public const BLOCK_META_USE_MULTIPLE_SLOTS = 'use_multiple_slots';
	public const BLOCK_META_SLOTS              = 'slots';
	public const BLOCK_META_SLOT               = 'slot';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Advertisement', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays an advertisement.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'schedule',
				'align'       => 'full', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'advertisement', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'full' ),
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
		$ad_slots = $this->get_ad_slots();

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Advertisement', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Select::make( _x( 'Advertisement slot', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_ADVERTISEMENT_SLOT )
						->helperText( _x( 'Select an advertisement slot from the list.', 'Field', 'fdmg-labels-admin' ) )
						->choices(
							array_combine(
								$ad_slots,
								$ad_slots
							)
						)
						->format( 'value' )
						->nullable()
						->required()
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::BLOCK_META_USE_MULTIPLE_SLOTS, '==', '0' ),
							)
						),
					TrueFalse::make( _x( 'Use multiple advertisement slots', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_USE_MULTIPLE_SLOTS )
						->helperText( _x( 'Check this box to select multple advertisement slots to render in this block.', 'Field', 'fdmg-labels-admin' ) )
						->stylized(),
					Repeater::make( _x( 'Advertisement slots', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SLOTS )
						->fields(
							array(
								Select::make( _x( 'Advertisement slot', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_SLOT )
									->helperText( _x( 'Select an advertisement slot from the list.', 'Field', 'fdmg-labels-admin' ) )
									->choices(
										array_combine(
											$ad_slots,
											$ad_slots
										)
									)
										->format( 'value' )
										->required(),
							)
						)
						->minRows( 2 )
						->button( _x( 'Add slot', 'Fields', 'fdmg-labels-admin' ) )
						->layout( 'table' )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::BLOCK_META_USE_MULTIPLE_SLOTS, '==', '1' ),
							)
						),
				),
				'location' => array(
					Location::where( 'block', 'acf/block-' . self::BLOCK_NAME ),
				),
			)
		);
	}

	/**
	 * @return array<string>
	 */
	protected function get_ad_slots(): array {
		return array(
			'Article/Header',
			'Article/Mid1',
			'Article/Mid2',
			'Article/Mid3',
			'Author/Header',
			'Author/Mid1',
			'Category/Header',
			'Category/Mid1',
			'Home/Header',
			'Home/Mid1',
			'Home/Mid2',
			'Home/Mid3',
			'Home/Mid4',
			'Home/Mid5',
			'Home/Mid6',
			'Home/Mid7',
			'Home/Mid8',
			'Home/Mid9',
			'Page/Header',
			'Page/Mid1',
			'Page/Mid2',
			'Partner/Header',
			'Partner/Mid1',
			'Partner/Mid2',
			'Searchresult/Header',
			'Searchresult/Mid1',
		);
	}
}
