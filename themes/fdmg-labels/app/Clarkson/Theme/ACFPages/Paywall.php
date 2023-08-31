<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WysiwygEditor;
use Extended\ACF\Location;

class Paywall {

	protected const FIELD_GROUP = 'paywall';

	public const MENU_SLUG                          = 'acf-paywall-options';
	public const META_PAYWALL                       = 'paywall';
	public const META_PAYWALL_TITLE                 = 'title';
	public const META_PAYWALL_TITLE_REQUIRED_ROLE   = 'title_required_role';
	public const META_PAYWALL_CONTENT               = 'content';
	public const META_PAYWALL_CONTENT_REQUIRED_ROLE = 'content_required_role';
	public const META_PAYWALL_LOGIN_BUTTON          = 'login_button';
	public const META_PAYWALL_SUBSCRIPTION_BUTTON   = 'subscription_button';
	public const META_PAYWALL_TAB_NOT_LOGGED_IN     = 'not_logged_in';
	public const META_PAYWALL_TAB_REQUIRED_ROLES    = 'required_roles';

	public function register_hooks(): void {

		if ( is_plugin_active( 'll-paywall/ll-paywall.php' ) ) {
			add_action( 'acf/init', array( $this, 'register' ) );
			add_action( 'acf/init', array( $this, 'register_fields' ) );
		}
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'menu_title'  => _x( 'Paywall', 'Options', 'fdmg-labels-admin' ),
				'page_title'  => _x( 'Paywall', 'Options', 'fdmg-labels-admin' ),
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
				'title'    => _x( 'Features', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Paywall', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL )
					->fields(
						array(
							Tab::make( _x( 'User is not logged in', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TAB_NOT_LOGGED_IN )
								->placement( 'left' ),
							Text::make( _x( 'Title of the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TITLE )
								->instructions( _x( 'The title of the paywall block', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
							WysiwygEditor::make( _x( 'Content inside the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_CONTENT )
								->instructions( _x( 'The content of the paywall block. Lists will be displayed as checkmark lists.', 'Fields', 'fdmg-labels-admin' ) )
								->tabs( 'visual' )
								->toolbar( 'basic' )
								->mediaUpload( false )
								->required(),
							Link::make( _x( 'Login button', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_LOGIN_BUTTON )
								->instructions( _x( 'The link to the login page.', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
							Tab::make( _x( 'When a user role is required', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TAB_REQUIRED_ROLES )
								->placement( 'left' ),
							Text::make( _x( 'Title of the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TITLE_REQUIRED_ROLE )
								->instructions( _x( 'The title of the paywall block', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
							WysiwygEditor::make( _x( 'Content inside the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_CONTENT_REQUIRED_ROLE )
								->instructions( _x( 'The content of the paywall block. Lists will be displayed as checkmark lists.', 'Fields', 'fdmg-labels-admin' ) )
								->tabs( 'visual' )
								->toolbar( 'basic' )
								->mediaUpload( false )
								->required(),
							Link::make( _x( 'Choose subscription button', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_SUBSCRIPTION_BUTTON )
								->instructions( _x( 'The link to the subscriptions page.', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
						),
					),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
