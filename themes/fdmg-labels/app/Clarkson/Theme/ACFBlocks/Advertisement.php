<?php
namespace Clarkson\Theme\ACFBlocks;

use Extended\ACF\Fields\Select;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Advertisement {
	public const BLOCK_NAME = 'advertisement';

	protected const FIELD_GROUP = 'block_advertisement';

	public const BLOCK_META_ADVERTISEMENT_SLOT = 'advertisement_slot';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Advertisement', 'Block', 'fdmg-labels' ),
				'description' => _x( 'Displays an advertisement.', 'Block', 'fdmg-labels' ),
				'icon'        => 'schedule',
				'align'       => 'full', // full, wide, left, center, right
				'keywords'    => array( 'advertisement', 'ad' ),
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
				'title'    => _x( 'Advertisement', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Select::make( _x( 'Advertisement slot', 'Fields', 'fdmg-labels' ), self::BLOCK_META_ADVERTISEMENT_SLOT )
						->instructions( _x( 'Select an advertisement slot from the list.', 'Field', 'fdmg-labels' ) )
						->choices(
							array_combine(
								$ad_slots,
								$ad_slots
							)
						)
						->returnFormat( 'value' )
						->allowNull()
						->required(),
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
			'Web/Header',
			'Home/Mid1',
			'Home/Mid2',
			'Home/Mid3',
			'Home/Mid4',
			'Home/Mid5',
			'Home/Mid6',
			'Article/Mid1',
			'Article/Mid2',
			'Category/Mid1',
			'Page/Mid1',
			'Page/Mid2',
			'Searchresult/Mid1',
			'Author/Mid1',
			'Partner/Mid1',
			'Partner/Mid2',
		);
	}
}
