<?php
namespace Clarkson\Theme\ACFWidgets;

use Clarkson\Theme\PostTypes\Vacancy;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Vacancies {
	public const WIDGET_NAME = 'vacancies';

	protected const FIELD_GROUP = 'widget_vacancies';

	public const WIDGET_META_USAGE           = 'usage';
	public const WIDGET_META_TITLE           = 'title';
	public const WIDGET_META_SELECTED_POSTS  = 'selected_posts';
	public const WIDGET_META_IS_FILL_ENABLED = 'is_fill_enabled';
	public const WIDGET_META_AMOUNT          = 'amount';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Vacancies/Jobs', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a list of vacancies based on multiple options.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'money',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'vacancies', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'jobs', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'        => base_block::PREVIEW_MODE_DATA,
							self::WIDGET_META_IS_FILL_ENABLED => true,
							self::WIDGET_META_AMOUNT => 2,
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
				'title'    => _x( 'Vacancies/Jobs', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Message::make( _x( 'Usage', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_USAGE )
						->message( _x( 'Displays a list of vacancies/jobs based on multiple options.', 'Fields', 'fdmg-labels-admin' ) )
						->newLines( 'wpautop' ),
					Text::make( _x( 'Use this field to overwrite the default "Jobs" title', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_TITLE ),
					Relationship::make( _x( 'Select vacancies to be sticky (optional)', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_SELECTED_POSTS )
						->postTypes( array( Vacancy::POST_TYPE_NAME ) )
						->filters(
							array(
								'search',
							)
						)
						->returnFormat( 'object' ),
					TrueFalse::make( _x( 'Allow this block to automatically fill with the latest vacancies', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_IS_FILL_ENABLED )
						->defaultValue( true )
						->stylisedUi(),
					Number::make( _x( 'Select the maximum amount of vacancies to be shown', 'Fields', 'fdmg-labels-admin' ), self::WIDGET_META_AMOUNT )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::WIDGET_META_IS_FILL_ENABLED, '==', '1' ),
							)
						)
						->defaultValue( 3 )
						->min( 1 )
						->step( 1 )
						->append( _x( 'vacancies', 'Fields', 'fdmg-labels-admin' ) )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
