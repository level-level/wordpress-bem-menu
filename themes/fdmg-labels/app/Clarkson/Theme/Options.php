<?php
namespace Clarkson\Theme;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Image;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Textarea;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Location;

class Options {

	public const FIELD_GROUP = 'theme_options';

	public const OPTION_GROUP                 = 'theme_options';
	public const OPTION_NEWSLETTER_TAB        = 'newsletter_tab';
	public const OPTION_NEWSLETTER_GROUP      = 'newsletter';
	public const OPTION_NEWSLETTER_TITLE      = 'title';
	public const OPTION_NEWSLETTER_TEXTAREA   = 'textarea';
	public const OPTION_NEWSLETTER_LINK       = 'link';
	public const OPTION_NEWSLETTER_IMAGE      = 'image';
	public const OPTION_COOKIEWALL_TAB        = 'cookiewall_tab';
	public const OPTION_SOURCEPOINT_ENABLE    = 'enable_sourcepoint_cmp';
	public const OPTION_ADVERTISEMENTS_TAB    = 'advertisements_tab';
	public const OPTION_ADVERTISEMENTS_ENABLE = 'enable_advertisements';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'add_options_pages' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/widget-newsletter', array( $this, 'add_context_args' ) );
		add_filter( 'clarkson_core_block_context_acf/block-advertisement', array( $this, 'add_context_args' ) );
	}

	public function add_options_pages(): void {
		acf_add_options_page(
			array(
				'menu_title' => _x( 'Theme options', 'Options', 'fdmg-labels' ),
				'page_title' => _x( 'Options', 'Options', 'fdmg-labels' ),
				'menu_slug'  => 'acf-options',
				'icon_url'   => 'dashicons-admin-settings',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Theme options', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Group::make( _x( 'Theme options', 'Fields', 'fdmg-labels' ), self::OPTION_GROUP )
						->fields(
							array(
								Tab::make( _x( 'Newsletter', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TAB )
									->placement( 'left' ),
								Group::make( _x( 'Newsletter', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_GROUP )
								->layout( 'block' )
								->fields(
									array(
										Text::make( _x( 'Title in the newsletter block', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TITLE ),
										Textarea::make( _x( 'Text in the newsletter block', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TEXTAREA )
											->newLines( 'wpautop' ),
										Link::make( _x( 'Button', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_LINK )
											->instructions( 'This button will be used in the header of the site and in the newsletter block.' )
											->returnFormat( 'array' ),
										Image::make( _x( 'Image in the newsletter block', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_IMAGE )
											->returnFormat( 'id' )
											->previewSize( 'medium' )
											->library( 'all' ),
									)
								),
								Tab::make( _x( 'Cookiewall', 'Fields', 'fdmg-labels' ), self::OPTION_COOKIEWALL_TAB )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Sourcepoint CMP', 'Fields', 'fdmg-labels' ), self::OPTION_SOURCEPOINT_ENABLE )
									->stylisedUi( _x( 'Enabled', 'Fields', 'fdmg-labels' ), _x( 'Disabled', 'Fields', 'fdmg-labels' ) )
									->defaultValue( 1 )
									->instructions( 'Enables or disables the Sourcepoint CMP cookiewall.' ),
								Tab::make( _x( 'Advertisements', 'Fields', 'fdmg-labels' ), self::OPTION_ADVERTISEMENTS_TAB )
									->placement( 'left' ),
								TrueFalse::make( _x( 'Advertisements', 'Fields', 'fdmg-labels' ), self::OPTION_ADVERTISEMENTS_ENABLE )
									->stylisedUi( _x( 'Enabled', 'Fields', 'fdmg-labels' ), _x( 'Disabled', 'Fields', 'fdmg-labels' ) )
									->defaultValue( 1 )
									->instructions( 'Enables or disables the advertisements' ),
							)
						),
				),
				'layout'   => 'block',
				'location' => array(
					Location::where( 'options_page', '==', 'acf-options' ),
				),
			)
		);
	}

	/**
	* Make global options available in the theme
	*/
	public function add_context_args( array $context ): array {
		$context['options'] = get_field( 'theme_options', 'options' ) ?: array();
		$context['socials'] = get_option( 'wpseo_social' ) ?: array();
		return $context;
	}
}
