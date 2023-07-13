<?php
namespace Clarkson\Theme\ACFWidgets;

use Clarkson\Theme\PostTypes\Vacancy;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\Relationship;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;
use Gutenberg\Blocks\base_block;

class Vacancies {
	public const WIDGET_NAME = 'vacancies';

	protected const FIELD_GROUP = 'widget_vacancies';

	public const WIDGET_META_USAGE            = 'usage';
	public const WIDGET_META_SELECTED_POSTS   = 'selected_posts';
	public const WIDGET_META_IS_FILL_DISABLED = 'is_fill_disabled';
	public const WIDGET_META_AMOUNT           = 'amount';


	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::WIDGET_NAME,
				'title'       => _x( 'Vacancies/Jobs', 'Block', 'fdmg-labels' ),
				'description' => _x( 'Displays a list of vacancies based on multiple options.', 'Block', 'fdmg-labels' ),
				'icon'        => 'money',
				'align'       => 'center', // full, wide, left, center, right
				'keywords'    => array( 'vacancies', 'jobs' ),
				'supports'    => array(
					'align'    => array( 'center' ),
					'multiple' => true,
				),
				'example'     => array(
					'attributes' => array(
						'data' => array(
							'll-preview-mode'  => base_block::PREVIEW_MODE_DATA,
							'is_fill_disabled' => false,
							'amount'           => 2,
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
				'title'    => _x( '[Widget] Vacancies/Jobs', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Message::make( _x( 'Usage', 'Fields', 'fdmg-labels' ), self::WIDGET_META_USAGE )
						->message( _x( 'Displays a list of vacancies/jobs based on multiple options.', 'Fields', 'fdmg-labels' ) )
						->newLines( 'wpautop' ),
					Relationship::make( _x( 'Select vacancies to be sticky (optional)', 'Fields', 'fdmg-labels' ), self::WIDGET_META_SELECTED_POSTS )
						->postTypes( array( Vacancy::POST_TYPE_NAME ) )
						->filters(
							array(
								'search',
							)
						)
						->returnFormat( 'object' ),
					TrueFalse::make( _x( 'Disallow this block to automatically fill with the latest vacancies', 'Fields', 'fdmg-labels' ), self::WIDGET_META_IS_FILL_DISABLED )
						->stylisedUi( _x( 'Yes', 'Fields', 'fdmg-labels' ), _x( 'No', 'Fields', 'fdmg-labels' ) ),
					Number::make( _x( 'Select the maximum amount of vacancies to be shown', 'Fields', 'fdmg-labels' ), self::WIDGET_META_AMOUNT )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::WIDGET_META_IS_FILL_DISABLED, '!=', '1' ),
							)
						)
						->defaultValue( 3 )
						->min( 1 )
						->step( 1 )
						->append( _x( 'vacancies', 'Fields', 'fdmg-labels' ) )
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::WIDGET_NAME ),
				),
			)
		);
	}
}
