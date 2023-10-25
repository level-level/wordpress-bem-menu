<?php
namespace Clarkson\Theme\ACFPages;

use Clarkson\Theme\Fields\Socials;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\Url;
use Extended\ACF\Location;

class ThemeOptions {

	protected const FIELD_GROUP = 'theme_options';

	public const MENU_SLUG = 'acf-options';

	public const OPTION_GROUP                 = 'theme_options';
	public const OPTION_NEWSLETTER_TAB        = 'newsletter_tab';
	public const OPTION_NEWSLETTER_GROUP      = 'newsletter';
	public const OPTION_NEWSLETTER_TITLE      = 'title';
	public const OPTION_NEWSLETTER_CONTENT    = 'content';
	public const OPTION_NEWSLETTER_BUTTON     = 'button';
	public const OPTION_NEWSLETTER_IMAGE      = 'image';
	public const OPTION_COOKIEWALL_TAB        = 'cookiewall_tab';
	public const OPTION_SOURCEPOINT_ENABLE    = 'enable_sourcepoint_cmp';
	public const OPTION_ADVERTISEMENTS_TAB    = 'advertisements_tab';
	public const OPTION_ADVERTISEMENTS_ENABLE = 'enable_advertisements';
	public const OPTION_SUBSCRIBE_TAB         = 'subscribe_tab';
	public const OPTION_SUBSCRIBE_BUTTON      = 'subscribe_button';
	public const OPTIONS_SOCIALS              = 'socials';
	public const OPTIONS_SOCIALS_TYPE         = 'type';
	public const OPTIONS_SOCIALS_URL          = 'url';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/widget-newsletter', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/block-advertisement', array( $this, 'add_context_args' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['options'] = get_field( self::OPTION_GROUP, 'options' ) ?: array();
		return $context;
	}

	public function register(): void {
		acf_add_options_page(
			array(
				'menu_title' => _x( 'Theme options', 'Options', 'fdmg-labels-admin' ),
				'page_title' => _x( 'Options', 'Options', 'fdmg-labels-admin' ),
				'menu_slug'  => self::MENU_SLUG,
				'icon_url'   => 'dashicons-admin-settings',
				'redirect'   => false,
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Theme options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Theme options', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP )
						->fields(
							array(
								Tab::make( _x( 'Newsletter', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_TAB )
										->placement( 'left' ),
								Group::make( _x( 'Newsletter', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_GROUP )
									->layout( 'block' )
								->fields(
									array(
										Text::make( _x( 'Title in the newsletter block', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_TITLE ),
										Textarea::make( _x( 'Text in the newsletter block', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_CONTENT )
											->newLines( 'wpautop' ),
										Link::make( _x( 'Button', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_BUTTON )
											->instructions( _x( 'This button will be used in the header of the site (when not using the compact header option) and in the newsletter block.', 'Fields', 'fdmg-labels-admin' ) )
											->returnFormat( 'array' ),
										Image::make( _x( 'Image in the newsletter block', 'Fields', 'fdmg-labels-admin' ), self::OPTION_NEWSLETTER_IMAGE )
											->returnFormat( 'id' )
											->previewSize( 'medium' )
											->library( 'all' ),
									)
								),
								Tab::make( _x( 'Cookiewall', 'Fields', 'fdmg-labels-admin' ), self::OPTION_COOKIEWALL_TAB )
										->placement( 'left' ),
								TrueFalse::make( _x( 'Sourcepoint CMP', 'Fields', 'fdmg-labels-admin' ), self::OPTION_SOURCEPOINT_ENABLE )
										->stylisedUi( _x( 'Enabled', 'Fields', 'fdmg-labels-admin' ), _x( 'Disabled', 'Fields', 'fdmg-labels-admin' ) )
										->defaultValue( 1 )
										->instructions( _x( 'Enables or disables the Sourcepoint CMP cookiewall.', 'Fields', 'fdmg-labels-admin' ) ),
								Tab::make( _x( 'Advertisements', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ADVERTISEMENTS_TAB )
										->placement( 'left' ),
								TrueFalse::make( _x( 'Advertisements', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ADVERTISEMENTS_ENABLE )
										->stylisedUi( _x( 'Enabled', 'Fields', 'fdmg-labels-admin' ), _x( 'Disabled', 'Fields', 'fdmg-labels-admin' ) )
										->defaultValue( 1 )
										->instructions( _x( 'Enables or disables the advertisements', 'Fields', 'fdmg-labels-admin' ) ),
								Tab::make( _x( 'Subscribe', 'Fields', 'fdmg-labels-admin' ), self::OPTION_SUBSCRIBE_TAB )
										->placement( 'left' ),
								Link::make( _x( 'Subscribe page', 'Fields', 'fdmg-labels-admin' ), self::OPTION_SUBSCRIBE_BUTTON )
										->instructions( _x( 'Once set, will display a button next to the navigation. Only when using the compact header option.', 'Fields', 'fdmg-labels-admin' ) )
										->returnFormat( 'array' ),
								Tab::make( _x( 'Social Media', 'Fields', 'fdmg-labels-admin' ) )
										->placement( 'left' ),
								Repeater::make( _x( 'Social Media', 'Fields', 'fdmg-labels-admin' ), self::OPTIONS_SOCIALS )
									->instructions( _x( 'Add links to your social media channels', 'Fields', 'fdmg-labels-admin' ) )
									->fields(
										array(
											Socials::get_list( self::OPTIONS_SOCIALS_TYPE ),
											Url::make( _x( 'URL', 'Fields', 'fdmg-labels-admin' ), self::OPTIONS_SOCIALS_URL )
												->placeholder( 'https://' )
												->required(),
										)
									)
									->buttonLabel( _x( 'Add social media channel', 'Fields', 'fdmg-labels-admin' ) )
									->layout( 'table' ),
							)
						),
				),
				'layout'   => 'block',
				'location' => array(
					Location::where( 'options_page', '==', self::MENU_SLUG ),
				),
			)
		);
	}
}
