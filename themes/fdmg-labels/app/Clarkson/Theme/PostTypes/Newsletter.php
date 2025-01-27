<?php
namespace Clarkson\Theme\PostTypes;

use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Extended\ACF\ConditionalLogic;
use Extended\ACF\Fields\Checkbox;
use Extended\ACF\Fields\DateTimePicker;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Message;
use Extended\ACF\Fields\Number;
use Extended\ACF\Fields\PostObject;
use Extended\ACF\Fields\RadioButton;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Taxonomy;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TimePicker;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;
use Extended\ACF\Key;
use Extended\ACF\Location;
use FDMGLabels\Connectors\Selligent\Settings\NewsletterPublicationSettings;
use FDMGLabels\Connectors\Selligent\Settings\NewsletterTypeSettings;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use FDMGLabels\FeatureManager;
use FDMGLabels\Newsletter\ArticleGroup;
use WP_Locale;

class Newsletter {
	public const POST_TYPE_NAME      = 'ii_newsletter';
	public const ALLOWED_CORE_BLOCKS = array(
		'core/paragraph',
		'core/heading',
	);

	protected const FIELD_GROUP_NEWSLETTER_META = 'newsletter_meta';

	public const META_MESSAGE                       = 'message';
	public const META_PUBLICATION                   = 'publication';
	public const META_TYPE                          = 'type';
	public const META_SUBJECT                       = 'subject';
	public const META_PRE_HEADER                    = 'pre_header';
	public const META_INTRODUCTION                  = 'introduction';
	public const META_ARTICLE_GROUPS_PREFIX         = 'article_groups_';
	public const META_ARTICLE_GROUP_PREFIX          = 'article_group_';
	public const META_ARTICLE_GROUP_SELECTION_TYPE  = 'selection_type';
	public const META_ARTICLE_GROUP_ARTICLE_COUNT   = 'article_count';
	public const META_ARTICLE_GROUP_ARTICLE_AGE     = 'max_age';
	public const META_ARTICLE_GROUP_SORT            = 'sort';
	public const META_ARTICLE_GROUP_FILTER_CATEGORY = 'filter_category';
	public const META_ARTICLE_GROUP_FILTER_GENRE    = 'filter_genre';
	public const META_ARTICLE_GROUP_FILTER_SECTION  = 'filter_section';
	public const META_ARTICLE_GROUP_ARTICLES        = 'articles';
	public const META_ARTICLE_GROUP_ARTICLE_ARTICLE = 'article';

	public const META_PREVIEW                              = 'preview';
	public const META_PREVIEW_ACTION                       = 'action';
	public const META_PREVIEW_RECIPIENTS                   = 'recipients';
	public const META_PREVIEW_RECIPIENTS_OPTION_ALL        = '0';
	public const META_PREVIEW_RECIPIENTS_OPTION_EDITORS    = '1';
	public const META_PREVIEW_RECIPIENTS_OPTION_DEVELOPERS = '2';

	public const META_SEND                            = 'send';
	public const META_SEND_ACTION                     = 'action';
	public const META_SEND_INTERVAL                   = 'interval';
	public const META_SEND_INTERVAL_OPTION_ONCE       = 'once';
	public const META_SEND_INTERVAL_OPTION_REPETITIVE = 'repetitive';

	public const META_SEND_ONCE        = 'once';
	public const META_SEND_ONCE_FUTURE = 'future';
	public const META_SEND_ONCE_DATE   = 'date';

	public const META_SEND_REPETITIVE          = 'repetitive';
	public const META_SEND_REPETITIVE_WEEKDAYS = 'weekdays';
	public const META_SEND_REPETITIVE_TIME     = 'time';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'acf/validate_value/name=' . self::META_INTRODUCTION, array( $this, 'wysiwyg_max_length' ), 10, 3 );
		add_filter( 'acf/validate_value/name=' . self::META_SEND_ONCE_DATE, array( $this, 'limit_schedule_dates' ), 10, 3 );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'               => _x( 'Newsletter', 'Post Type', 'fdmg-labels-admin' ),
				'singular_name'      => _x( 'Newsletter', 'Post Type', 'fdmg-labels-admin' ),
				'all_items'          => _x( 'All newsletters', 'Post Type', 'fdmg-labels-admin' ),
				'archives'           => _x( 'Newsletter archives', 'Post Type', 'fdmg-labels-admin' ),
				'add_new'            => _x( 'New newsletter', 'Post Type', 'fdmg-labels-admin' ),
				'edit_item'          => _x( 'Edit newsletter', 'Post Type', 'fdmg-labels-admin' ),
				'view_item'          => _x( 'View newsletter', 'Post Type', 'fdmg-labels-admin' ),
				'view_items'         => _x( 'View newsletters', 'Post Type', 'fdmg-labels-admin' ),
				'search_items'       => _x( 'Search newsletters', 'Post Type', 'fdmg-labels-admin' ),
				'not_found'          => _x( 'No newsletters found', 'Post Type', 'fdmg-labels-admin' ),
				'not_found_in_trash' => _x( 'No newsletters found in trash', 'Post Type', 'fdmg-labels-admin' ),
				'parent_item_colon'  => _x( 'Parent newsletter:', 'Post Type', 'fdmg-labels-admin' ),
			),
			'public'                => false,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'supports'              => array( 'title', 'revisions' ),
			'has_archive'           => false,
			'rewrite'               => array(
				'slug'       => __( 'newsletters', 'fdmg-labels-slugs' ),
				'with_front' => false,
			),
			'query_var'             => true,
			'menu_position'         => null,
			'menu_icon'             => 'dashicons-email-alt2',
			'show_in_rest'          => false,
			'rest_base'             => self::POST_TYPE_NAME,
			'rest_controller_class' => 'WP_REST_Posts_Controller',
		);

		register_post_type( self::POST_TYPE_NAME, $args );
	}

	public function register_fields(): void {
		/** @var WP_Locale */
		global $wp_locale;

		$fields = array(
			Message::make( __( 'Creating a newsletter', 'fdmg-labels-admin' ), self::META_MESSAGE )
				->body( _x( 'Fill in the required fields and select the posts that need to be included in the newsletter. Press \'Publish\' or \'Update\' to save the changes. If the \'Send the newsletter\' box is enabled, the newsletter will be sent to Selligent.', 'Fields', 'fdmg-labels-admin' ) ),
		);

		$publications        = Settings::get_instance()->get_newsletter()->get_publications();
		$publication_choices = array_map(
			function ( NewsletterPublicationSettings $publication_settings ): string {
				return $publication_settings->get_name();
			}, $publications
		);
		$fields[]            = Select::make( _x( 'Publication', 'Fields', 'fdmg-labels-admin' ), self::META_PUBLICATION )
			->choices( array_combine( $publication_choices, $publication_choices ) )
			->required();

		$types        = Settings::get_instance()->get_newsletter()->get_types();
		$type_choices = array_map(
			function ( NewsletterTypeSettings $type_settings ): string {
				return $type_settings->get_type();
			}, $types
		);
		$fields[]     = Select::make( _x( 'Type', 'Fields', 'fdmg-labels-admin' ), self::META_TYPE )
			->choices( array_combine( $type_choices, $type_choices ) )
			->required();

		$fields[] = Text::make( _x( 'Subject', 'Fields', 'fdmg-labels-admin' ), self::META_SUBJECT )
			->helperText( _x( 'The subject of the e-mail.', 'Fields', 'fdmg-labels-admin' ) )
			->maxLength( 255 )
			->required();

		$fields[] = Text::make( _x( 'Pre header', 'Fields', 'fdmg-labels-admin' ), self::META_PRE_HEADER )
			->helperText( _x( 'The text that is shown in the list view of an e-mail client.', 'Fields', 'fdmg-labels-admin' ) )
			->maxLength( 255 );

		$fields[] = WYSIWYGEditor::make( _x( 'Introduction', 'Fields', 'fdmg-labels-admin' ), self::META_INTRODUCTION )
			->helperText( _x( 'The text that will be shown below the title.', 'Fields', 'fdmg-labels-admin' ) )
			->toolbar( 'basic' )
			->tabs( 'visual' )
			->disableMediaUpload();

		foreach ( $types as $type ) {
			$article_group_fields = $this->get_type_article_group_fields( $type );

			$fields[] = Group::make( sprintf( _x( '%s article groups', 'Fields', 'fdmg-labels-admin' ), $type->get_type() ), self::META_ARTICLE_GROUPS_PREFIX . Key::sanitize( $type->get_type() ) )
				->fields( $article_group_fields )
				->conditionalLogic(
					array(
						ConditionalLogic::where( self::META_TYPE, '==', $type->get_type() ),
					)
				);
		}

		$fields[] = Group::make( _x( 'Preview newsletter', 'Fields', 'fdmg-labels-admin' ), self::META_PREVIEW )
			->fields(
				array(
					TrueFalse::make( _x( 'Send preview', 'Fields', 'fdmg-labels-admin' ), self::META_PREVIEW_ACTION )
						->stylized(),
					Select::make( _x( 'Preview recipients', 'Fields', 'fdmg-labels-admin' ), self::META_PREVIEW_RECIPIENTS )
						->choices(
							array(
								self::META_PREVIEW_RECIPIENTS_OPTION_EDITORS => _x( 'Editorial team', 'Fields', 'fdmg-labels-admin' ),
								self::META_PREVIEW_RECIPIENTS_OPTION_DEVELOPERS => _x( 'Developer team', 'Fields', 'fdmg-labels-admin' ),
							)
						)
						->default( self::META_PREVIEW_RECIPIENTS_OPTION_EDITORS )
						->required()
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::META_PREVIEW . '_' . self::META_PREVIEW_ACTION, '==', '1' ),
							)
						),
				)
			);

		$weekday_options = array(
			'1' => $wp_locale->get_weekday( 1 ),
			'2' => $wp_locale->get_weekday( 2 ),
			'3' => $wp_locale->get_weekday( 3 ),
			'4' => $wp_locale->get_weekday( 4 ),
			'5' => $wp_locale->get_weekday( 5 ),
			'6' => $wp_locale->get_weekday( 6 ),
			'0' => $wp_locale->get_weekday( 0 ),
		);
		$fields[]        = Group::make( _x( 'Send newsletter', 'Fields', 'fdmg-labels-admin' ), self::META_SEND )
			->fields(
				array(
					TrueFalse::make( _x( 'Send the newsletter', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_ACTION )
						->stylized(),
					RadioButton::make( _x( 'Interval', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_INTERVAL )
						->choices(
							array(
								self::META_SEND_INTERVAL_OPTION_ONCE => _x( 'Once', 'Fields', 'fdmg-labels-admin' ),
								self::META_SEND_INTERVAL_OPTION_REPETITIVE => _x( 'Repetitive', 'Fields', 'fdmg-labels-admin' ),
							)
						)
						->default( self::META_SEND_INTERVAL_OPTION_ONCE )
						->required()
						->layout( 'horizontal' )
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::META_SEND . '_' . self::META_SEND_ACTION, '==', '1' ),
							)
						),
					Group::make( _x( 'Send once', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_ONCE )
						->fields(
							array(
								TrueFalse::make( _x( 'Schedule in the future', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_ONCE_FUTURE )
									->helperText( _x( 'Enable this option to schedule a date in the future to send the newsletter. Leave disabled to schedule sending directly.', 'Fields', 'fdmg-labels-admin' ) )
									->stylized()
									->default( false ),
								DateTimePicker::make( _x( 'Sending date and time', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_ONCE_DATE )
									->required()
									->format( 'Y-m-d H:i:s' )
									->displayFormat( sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) ) )
									->conditionalLogic(
										array(
											ConditionalLogic::where( self::META_SEND . '_' . self::META_SEND_ONCE . '_' . self::META_SEND_ONCE_FUTURE, '==', '1' ),
										)
									),
							)
						)
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::META_SEND . '_' . self::META_SEND_INTERVAL, '==', self::META_SEND_INTERVAL_OPTION_ONCE ),
							)
						),
					Group::make( _x( 'Send repetitive', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_REPETITIVE )
						->fields(
							array(
								Checkbox::make( _x( 'Weekdays', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_REPETITIVE_WEEKDAYS )
									->choices( $weekday_options )
									->default( array() )
									->required()
									->layout( 'horizontal' ),
								TimePicker::make( _x( 'Time', 'Fields', 'fdmg-labels-admin' ), self::META_SEND_REPETITIVE_TIME )
									->required()
									->format( 'H:i:s' )
									->displayFormat( get_option( 'time_format' ) ),
							)
						)
						->conditionalLogic(
							array(
								ConditionalLogic::where( self::META_SEND . '_' . self::META_SEND_INTERVAL, '==', self::META_SEND_INTERVAL_OPTION_REPETITIVE ),
							)
						),
				)
			);

		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP_NEWSLETTER_META,
				'title'    => _x( 'Newsletter meta', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => $fields,
				'location' => array(
					Location::where( 'post_type', '==', self::POST_TYPE_NAME ),
				),
				'position' => 'normal',
			)
		);
	}

	/**
	 * Get article group fields for a newsletter type
	 *
	 * @param NewsletterTypeSettings $type
	 * @return Group[]
	 */
	protected function get_type_article_group_fields( NewsletterTypeSettings $type ): array {
		$article_group_fields = array();
		foreach ( $type->get_article_groups() as $index => $article_group ) {
			$article_group_key          = self::META_ARTICLE_GROUP_PREFIX . $index;
			$article_group_field_fields = array(
				Select::make( _x( 'Selection type', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_SELECTION_TYPE )
					->choices(
						array(
							ArticleGroup::SELECTION_TYPE_MANUAL => _x( 'Manual', 'Fields', 'fdmg-labels-admin' ),
							ArticleGroup::SELECTION_TYPE_AUTOMATIC => _x( 'Automatic', 'Fields', 'fdmg-labels-admin' ),
						)
					)
					->default( ArticleGroup::SELECTION_TYPE_MANUAL )
					->required(),
				Repeater::make( _x( 'Articles', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_ARTICLES )
					->helperText( sprintf( _x( 'Add the articles that should be shown in the "%s" article group of the newsletter.', 'Fields', 'fdmg-labels-admin' ), $article_group->get_name() ) )
					->minRows( 0 )
					->maxRows( 10 )
					->collapsed( 'article' )
					->button( _x( 'Add article', 'Fields', 'fdmg-labels-admin' ) )
					->fields(
						array(
							PostObject::make( _x( 'Article', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_ARTICLE_ARTICLE )
								->postTypes( array( Post::POST_TYPE_NAME ) )
								->format( 'id' )
								->required(),
						)
					)
					->conditionalLogic(
						array(
							ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_MANUAL ),
						)
					),
				Select::make( _x( 'Sort by', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_SORT )
					->choices(
						array(
							ArticleGroup::SORT_RECENT      => _x( 'Most recent', 'Fields', 'fdmg-labels-admin' ),
							ArticleGroup::SORT_MOST_VIEWED => _x( 'Most viewed', 'Fields', 'fdmg-labels-admin' ),
						)
					)
					->default( ArticleGroup::SORT_RECENT )
					->required()->conditionalLogic(
						array(
							ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
						)
					),
				Number::make( _x( 'Maximum number of articles', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_ARTICLE_COUNT )
					->min( 1 )
					->max( 10 )
					->required()
					->conditionalLogic(
						array(
							ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
						)
					),
				Number::make( _x( 'Maximum age of articles (in days)', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_ARTICLE_AGE )
					->min( 1 )
					->max( 31 )
					->suffix( _x( 'days', 'Fields', 'fdmg-labels-admin' ) )
					->required()
					->conditionalLogic(
						array(
							ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
						)
					),

			);

				$article_group_field_taxonomy_fields = array();
			if ( ( new FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
				$article_group_field_taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more genres to filter articles (optional)', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_FILTER_GENRE )
				->conditionalLogic(
					array(
						ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
					)
				)
				->taxonomy( Genre::TAXONOMY_NAME )
				->appearance( 'multi_select' )
				->nullable()
				->create( false )
				->save( false )
				->load( false )
				->format( 'object' );
			}

			if ( ( new FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
				$article_group_field_taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more sections to filter articles (optional)', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_FILTER_SECTION )
				->conditionalLogic(
					array(
						ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
					)
				)
				->taxonomy( Section::TAXONOMY_NAME )
				->appearance( 'multi_select' )
				->nullable()
				->create( false )
				->save( false )
				->load( false )
				->format( 'object' );
			}

			if ( ! ( new FeatureManager() )->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
				$article_group_field_taxonomy_fields[] = Taxonomy::make( _x( 'Select one or more categories to filter articles (optional)', 'Fields', 'fdmg-labels-admin' ), self::META_ARTICLE_GROUP_FILTER_CATEGORY )
				->conditionalLogic(
					array(
						ConditionalLogic::where( sprintf( '%s_%s', $article_group_key, self::META_ARTICLE_GROUP_SELECTION_TYPE ), '==', ArticleGroup::SELECTION_TYPE_AUTOMATIC ),
					)
				)
				->taxonomy( Category::TAXONOMY_NAME )
				->appearance( 'multi_select' )
				->nullable()
				->create( false )
				->save( false )
				->load( false )
				->format( 'object' );
			}

			$article_group_fields[] = Group::make( sprintf( _x( 'Article group %1$d: %2$s', 'Fields', 'fdmg-labels-admin' ), $index + 1, $article_group->get_name() ), $article_group_key )
				->fields(
					array_merge( $article_group_field_fields, $article_group_field_taxonomy_fields )
				);
		}
		return $article_group_fields;
	}

	/**
	 * Add validation check on field length for wysiwyg fields
	 *
	 * @param string|bool $valid
	 * @param mixed $value
	 * @param array $field
	 * @return string|bool
	 */
	public function wysiwyg_max_length( $valid, $value, $field ) {
		if ( ! $valid || ! is_string( $value ) || $field['type'] !== 'wysiwyg' ) {
			return $valid;
		}

		if ( strlen( $value ) > 2000 ) {
			$valid = _x( 'The HTML of this field may not be longer than 2000 characters.', 'Fields', 'fdmg-labels-admin' );
		}
		return $valid;
	}

	/**
	 * Only allow dates to be in the future and a maximum of 7 days ahead.
	 *
	 * @param string|bool $valid
	 * @param mixed $value
	 * @param array $field
	 * @return string|bool
	 */
	public function limit_schedule_dates( $valid, $value, $field ) {
		if ( ! $valid || ! is_string( $value ) || $field['type'] !== 'date_time_picker' ) {
			return $valid;
		}

		$datetime = date_create_from_format( 'Y-m-d H:i:s', $value, wp_timezone() );
		if ( ! $datetime ) {
			$valid = _x( 'The chosen date is invalid.', 'Fields', 'fdmg-labels-admin' );
		}

		if ( $datetime->getTimestamp() < time() ) {
			$valid = _x( 'The chosen date must be in the future.', 'Fields', 'fdmg-labels-admin' );
		}
		if ( $datetime->getTimestamp() > time() + ( DAY_IN_SECONDS * 7 ) ) {
			$valid = _x( 'The chosen date may not be more than 7 days from now.', 'Fields', 'fdmg-labels-admin' );
		}
		return $valid;
	}
}
