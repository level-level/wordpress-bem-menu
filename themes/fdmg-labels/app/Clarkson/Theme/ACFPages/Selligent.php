<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Location;
use Extended\ACF\Fields\Url;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Password;
use Extended\ACF\Fields\Number;

class Selligent {

	protected const FIELD_GROUP = 'selligent';

	public const MENU_SLUG = 'acf-selligent-options';

	public const OPTION_GROUP = 'selligent';
	public const OPTION_BASE_URL = 'base_url';
	public const OPTION_USERNAME = 'username';
	public const OPTION_PASSWORD = 'password';

	public const OPTION_GATES = 'gates';
	public const OPTION_GATE_SEND_NEWSLETTER = 'send_newsletter';
	public const OPTION_GATE_OPTIN = 'optin';

	public const OPTION_LIST_IDS = 'list_ids';
	public const OPTION_LIST_ID_ARTICLE = 'article';
	public const OPTION_LIST_ID_NEWSLETTER = 'newsletter';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Selligent', 'Options', 'fdmg-labels' ),
				'page_title'  => _x( 'Selligent', 'Options', 'fdmg-labels' ),
				'menu_slug'   => self::MENU_SLUG,
				'parent_slug' => ThemeOptions::MENU_SLUG,
				'icon_url'    => 'dashicons-admin-settings',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Selligent options', 'Fields', 'fdmg-labels' ),
				'fields'   => array(
					Group::make( _x( 'Selligent options', 'Fields', 'fdmg-labels' ), self::OPTION_GROUP )
						->fields(
							array(
								Url::make( _x( 'Selligent base URL', 'Fields', 'fdmg-labels' ), self::OPTION_BASE_URL )
									->placeholder( 'https://messagent.fdmediagroep.nl' ),
								Text::make( _x( 'Username', 'Fields', 'fdmg-labels' ), self::OPTION_USERNAME ),
								Password::make( _x( 'Password', 'Fields', 'fdmg-labels' ), self::OPTION_PASSWORD ),
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
