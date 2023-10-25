<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Password;
use Extended\ACF\Fields\Repeater;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\Url;
use Extended\ACF\Location;

class Selligent {

	protected const FIELD_GROUP = 'selligent';

	public const MENU_SLUG = 'acf-selligent-options';

	public const OPTION_GROUP    = 'selligent';
	public const OPTION_BASE_URL = 'base_url';
	public const OPTION_USERNAME = 'username';
	public const OPTION_PASSWORD = 'password';

	public const OPTION_GATES                = 'gates';
	public const OPTION_GATE_SEND_NEWSLETTER = 'send_newsletter';
	public const OPTION_GATE_OPTIN           = 'optin';

	public const OPTION_LIST_IDS           = 'list_ids';
	public const OPTION_LIST_ID_ARTICLE    = 'article';
	public const OPTION_LIST_ID_NEWSLETTER = 'newsletter';

	public const OPTION_NEWSLETTER                         = 'newsletter';
	public const OPTION_NEWSLETTER_PUBLICATIONS            = 'publications';
	public const OPTION_NEWSLETTER_PUBLICATION_NAME        = 'name';
	public const OPTION_NEWSLETTER_TYPES                   = 'types';
	public const OPTION_NEWSLETTER_TYPE_TYPE               = 'type';
	public const OPTION_NEWSLETTER_TYPE_ARTICLE_GROUPS     = 'article_groups';
	public const OPTION_NEWSLETTER_TYPE_ARTICLE_GROUP_NAME = 'name';

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
				'parent_slug' => SiteSettings::MENU_SLUG,
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
									->placeholder( 'https://messagent.fdmediagroep.nl' )
									->required(),
								Text::make( _x( 'Username', 'Fields', 'fdmg-labels' ), self::OPTION_USERNAME )
									->required(),
								Password::make( _x( 'Password', 'Fields', 'fdmg-labels' ), self::OPTION_PASSWORD )
									->required(),
								Group::make( _x( 'Gates', 'Fields', 'fdmg-labels' ), self::OPTION_GATES )
									->fields(
										array(
											Text::make( _x( 'Send newsletter', 'Fields', 'fdmg-labels' ), self::OPTION_GATE_SEND_NEWSLETTER )
												->required(),
											Text::make( _x( 'Optin', 'Fields', 'fdmg-labels' ), self::OPTION_GATE_OPTIN )
												->required(),
										)
									),
								Group::make( _x( 'List ID\'s', 'Fields', 'fdmg-labels' ), self::OPTION_LIST_IDS )
									->fields(
										array(
											Text::make( _x( 'Newsletter', 'Fields', 'fdmg-labels' ), self::OPTION_LIST_ID_NEWSLETTER )
												->required(),
											Text::make( _x( 'Article', 'Fields', 'fdmg-labels' ), self::OPTION_LIST_ID_ARTICLE )
												->required(),
										)
									),
								Group::make( _x( 'Newsletter', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER )
									->fields(
										array(
											Repeater::make( _x( 'Publications', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_PUBLICATIONS )
												->min( 1 )
												->fields(
													array(
														Text::make( _x( 'Name', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_PUBLICATION_NAME )
															->required(),
													)
												)
												->collapsed( self::OPTION_NEWSLETTER_PUBLICATION_NAME )
												->required(),
											Repeater::make( _x( 'Types', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TYPES )
												->min( 1 )
												->fields(
													array(
														Text::make( _x( 'Type', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TYPE_TYPE )
															->required(),
														Repeater::make( _x( 'Article groups', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TYPE_ARTICLE_GROUPS )
															->fields(
																array(
																	Text::make( _x( 'Name', 'Fields', 'fdmg-labels' ), self::OPTION_NEWSLETTER_TYPE_ARTICLE_GROUP_NAME )
																		->required(),
																)
															)
															->collapsed( self::OPTION_NEWSLETTER_TYPE_ARTICLE_GROUP_NAME ),
													)
												)
												->collapsed( self::OPTION_NEWSLETTER_TYPE_TYPE )
												->required(),
										)
									),
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
