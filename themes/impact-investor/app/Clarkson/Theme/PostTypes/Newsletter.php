<?php
namespace Clarkson\Theme\PostTypes;

use WordPlate\Acf\ConditionalLogic;
use WordPlate\Acf\Fields\DateTimePicker;
use WordPlate\Acf\Fields\Group;
use WordPlate\Acf\Fields\Message;
use WordPlate\Acf\Fields\PostObject;
use WordPlate\Acf\Fields\Repeater;
use WordPlate\Acf\Fields\Text;
use WordPlate\Acf\Fields\TrueFalse;
use WordPlate\Acf\Fields\WysiwygEditor;
use WordPlate\Acf\Location;

class Newsletter {
	public const POST_TYPE_NAME      = 'ii_newsletter';
	public const ALLOWED_CORE_BLOCKS = array(
		'core/paragraph',
		'core/heading',
	);

	public const META_MESSAGE          = 'message';
	public const META_SUBJECT          = 'subject';
	public const META_PRE_HEADER       = 'pre_header';
	public const META_INTRODUCTION     = 'introduction';
	public const META_ARTICLES         = 'articles';
	public const META_ARTICLES_ARTICLE = 'article';
	public const META_SEND             = 'send';
	public const META_SEND_ACTION      = 'action';
	public const META_SEND_TEST        = 'test';
	public const META_SEND_SCHEDULE    = 'schedule';
	public const META_SEND_DATE        = 'date';

	public function register_hooks(): void {
		add_action( 'init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'acf/validate_value/name=' . self::META_INTRODUCTION, array( $this, 'wysiwyg_max_length' ), 10, 3 );
		add_filter( 'acf/validate_value/name=' . self::META_SEND_DATE, array( $this, 'limit_schedule_dates' ), 10, 3 );
	}

	public function register(): void {
		$args = array(
			'labels'                => array(
				'name'                  => _x( 'Newsletter', 'Post Type', 'impact-investor' ),
				'singular_name'         => _x( 'Newsletter', 'Post Type', 'impact-investor' ),
				'all_items'             => _x( 'All newsletters', 'Post Type', 'impact-investor' ),
				'archives'              => _x( 'Newsletter archives', 'Post Type', 'impact-investor' ),
				'attributes'            => _x( 'Newsletter attributes', 'Post Type', 'impact-investor' ),
				'insert_into_item'      => _x( 'Insert into newsletter', 'Post Type', 'impact-investor' ),
				'uploaded_to_this_item' => _x( 'Uploaded to this newsletter', 'Post Type', 'impact-investor' ),
				'featured_image'        => _x( 'Featured image', 'Post Type', 'impact-investor' ),
				'filter_items_list'     => _x( 'Filter newsletters list', 'Post Type', 'impact-investor' ),
				'items_list_navigation' => _x( 'Newsletter list navigation', 'Post Type', 'impact-investor' ),
				'items_list'            => _x( 'Newsletter list', 'Post Type', 'impact-investor' ),
				'new_item'              => _x( 'New newsletter', 'Post Type', 'impact-investor' ),
				'add_new'               => _x( 'New newsletter', 'Post Type', 'impact-investor' ),
				'add_new_item'          => _x( 'Add new newsletter', 'Post Type', 'impact-investor' ),
				'edit_item'             => _x( 'Edit newsletter', 'Post Type', 'impact-investor' ),
				'view_item'             => _x( 'View newsletter', 'Post Type', 'impact-investor' ),
				'view_items'            => _x( 'View newsletters', 'Post Type', 'impact-investor' ),
				'search_items'          => _x( 'Search newsletters', 'Post Type', 'impact-investor' ),
				'not_found'             => _x( 'No newsletters found', 'Post Type', 'impact-investor' ),
				'not_found_in_trash'    => _x( 'No newsletters found in trash', 'Post Type', 'impact-investor' ),
				'parent_item_colon'     => _x( 'Parent newsletter:', 'Post Type', 'impact-investor' ),
				'menu_name'             => _x( 'Newsletters', 'Post Type', 'impact-investor' ),
			),
			'public'                => false,
			'hierarchical'          => false,
			'show_ui'               => true,
			'show_in_nav_menus'     => false,
			'supports'              => array( 'title', 'revisions' ),
			'has_archive'           => false,
			'rewrite'               => array(
				'slug'       => __( 'newsletters', 'impact-investor-slugs' ),
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
		register_extended_field_group(
			array(
				'title'    => _x( 'Newsletter meta', 'Fields', 'impact-investor' ),
				'fields'   => array(
					Message::make( _x( 'Creating a newsletter', 'impact-investor' ), self::META_MESSAGE )
						->message( _x( 'Fill in the required fields and select the posts that need to be included in the newsletter. Press \'Publish\' or \'Update\' to save the changes. If the \'Send newsletter\' box is enabled, the newsletter will be sent to Selligent.', 'Fields', 'impact-investor' ) ),
					Text::make( _x( 'Subject', 'Fields', 'impact-investor' ), self::META_SUBJECT )
						->instructions( _x( 'The subject of the e-mail.', 'Fields', 'impact-investor' ) )
						->characterLimit( 255 )
						->required(),
					Text::make( _x( 'Pre header', 'Fields', 'impact-investor' ), self::META_PRE_HEADER )
						->instructions( _x( 'The text that is shown in the list view of an e-mail client.', 'Fields', 'impact-investor' ) )
						->characterLimit( 255 ),
					WysiwygEditor::make( _x( 'Introduction', 'Fields', 'impact-investor' ), self::META_INTRODUCTION )
						->instructions( _x( 'The text that will be shown below the title.', 'Fields', 'impact-investor' ) )
						->toolbar( 'basic' )
						->tabs( 'visual' )
						->mediaUpload( false ),
					Repeater::make( _x( 'Articles', 'Fields', 'impact-investor' ), self::META_ARTICLES )
						->instructions( _x( 'Add the articles that should be shown in the newsletter.', 'Fields', 'impact-investor' ) )
						->min( 0 )
						->max( 6 )
						->collapsed( 'article' )
						->buttonLabel( _x( 'Add article', 'Fields', 'impact-investor' ) )
						->fields(
							array(
								PostObject::make( _x( 'Article', 'Fields', 'impact-investor' ), self::META_ARTICLES_ARTICLE )
									->postTypes( array( Post::POST_TYPE_NAME ) )
									->returnFormat( 'id' )
									->required(),
							)
						),
					Group::make( _x( 'Sending newsletter', 'Fields', 'impact-investor' ), self::META_SEND )
						->fields(
							array(
								TrueFalse::make( _x( 'Send the newsletter', 'Fields', 'impact-investor' ), self::META_SEND_ACTION )
									->stylisedUi(),
								TrueFalse::make( _x( 'Send as a test newsletter', 'Fields', 'impact-investor' ), self::META_SEND_TEST )
									->stylisedUi()
									->defaultValue( true )
									->conditionalLogic(
										array(
											ConditionalLogic::if( self::META_SEND . '_' . self::META_SEND_ACTION )->equals( 1 ),
										)
									),
								TrueFalse::make( _x( 'Schedule sending the newsletter', 'Fields', 'impact-investor' ), self::META_SEND_SCHEDULE )
									->instructions( _x( 'Enable this option to schedule a date in the future to send the newsletter. Leave disabled to schedule sending directly.', 'Fields', 'impact-investor' ) )
									->stylisedUi()
									->defaultValue( false )
									->conditionalLogic(
										array(
											ConditionalLogic::if( self::META_SEND . '_' . self::META_SEND_ACTION )->equals( 1 ),
										)
									),
								DateTimePicker::make( _x( 'Sending date and time', 'Fields', 'impact-investor' ), self::META_SEND_DATE )
									->required()
									->returnFormat( 'Y-m-d H:i:s' )
									->displayFormat( sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) ) )
									->conditionalLogic(
										array(
											ConditionalLogic::if( self::META_SEND . '_' . self::META_SEND_SCHEDULE )->equals( 1 ),
										)
									),
							)
						),
				),
				'location' => array(
					Location::if( 'post_type', self::POST_TYPE_NAME ),
				),
				'position' => 'normal',
			)
		);
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
			$valid = _x( 'The HTML of this field may not be longer than 2000 characters.', 'Fields', 'impact-investor' );
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
			$valid = _x( 'The chosen date is invalid.', 'Fields', 'impact-investor' );
		}

		if ( $datetime->getTimestamp() < time() ) {
			$valid = _x( 'The chosen date must be in the future.', 'Fields', 'impact-investor' );
		}
		if ( $datetime->getTimestamp() > time() + ( DAY_IN_SECONDS * 7 ) ) {
			$valid = _x( 'The chosen date may not be more than 7 days from now.', 'Fields', 'impact-investor' );
		}
		return $valid;
	}
}
