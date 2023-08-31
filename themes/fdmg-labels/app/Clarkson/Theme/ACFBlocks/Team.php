<?php
namespace Clarkson\Theme\ACFBlocks;

use Clarkson\Theme\PostTypes\Team as PostTypesTeam;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Team {
	public const BLOCK_NAME = 'team';

	protected const FIELD_GROUP = 'block_team';

	public const BLOCK_META_TITLE = 'title';
	public const BLOCK_META_TEAM  = 'team';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'block-' . self::BLOCK_NAME,
				'title'       => _x( 'Team', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of team members.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'groups',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'team', 'editors', 'members', 'leden' ),
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
				'title'    => _x( 'Team', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Use this title to overwrite the default title: \'Meet our editorial team\'', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE ),
					Relationship::make( _x( 'Select the team members', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TEAM )
						->postTypes( array( PostTypesTeam::POST_TYPE_NAME ) )
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
