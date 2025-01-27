<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Link;
use Extended\ACF\Fields\Tab;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\TrueFalse;
use Extended\ACF\Fields\WYSIWYGEditor;
use Extended\ACF\Location;

class Paywall {

	protected const FIELD_GROUP = 'paywall';

	public const MENU_SLUG                          = 'acf-paywall-options';
	public const META_PAYWALL                       = 'paywall';
	public const META_PAYWALL_DISABLED              = 'disabled';
	public const META_PAYWALL_TITLE                 = 'title';
	public const META_PAYWALL_TITLE_REQUIRED_ROLE   = 'title_required_role';
	public const META_PAYWALL_CONTENT               = 'content';
	public const META_PAYWALL_CONTENT_REQUIRED_ROLE = 'content_required_role';
	public const META_PAYWALL_SUBSCRIPTION_BUTTON   = 'subscription_button';
	public const META_PAYWALL_TAB_NOT_LOGGED_IN     = 'not_logged_in';
	public const META_PAYWALL_TAB_REQUIRED_ROLES    = 'required_roles';
	public const META_PAYWALL_TAB_GENERAL_OPTIONS   = 'general_options';
	public const META_PAYWALL_DEFAULT_ACTIVE        = 'payall_is_active_by_default';

	public function register_hooks(): void {
		require_once ABSPATH . '/wp-admin/includes/plugin.php';
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
							Tab::make( _x( 'General options', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TAB_GENERAL_OPTIONS )
								->placement( 'left' ),
							TrueFalse::make( _x( 'Disable the paywall for all articles', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_DISABLED )
								->helperText( _x( 'By selecting this option, content after a paywall block can be viewed by anyone.', 'Fields', 'fdmg-labels-admin' ) )
								->stylized()
								->default( false ),
							TrueFalse::make( _x( 'By default, all news articles are behind a paywall', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_DEFAULT_ACTIVE )
								->helperText( _x( 'By enabling this option, all new articles are drafted with the paywall block included.', 'Fields', 'fdmg-labels-admin' ) )
								->stylized()
								->default( true ),
							Tab::make( _x( 'When logging in is required', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TAB_NOT_LOGGED_IN )
								->placement( 'left' ),
							Text::make( _x( 'Title of the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TITLE )
								->helperText( _x( 'The title of the paywall block', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
							WYSIWYGEditor::make( _x( 'Content inside the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_CONTENT )
								->helperText( _x( 'The content of the paywall block. Lists will be displayed as checkmark lists.', 'Fields', 'fdmg-labels-admin' ) )
								->tabs( 'visual' )
								->toolbar( 'basic' )
								->disableMediaUpload()
								->required(),
							Tab::make( _x( 'When a user role is required', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TAB_REQUIRED_ROLES )
								->placement( 'left' ),
							Text::make( _x( 'Title of the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_TITLE_REQUIRED_ROLE )
								->helperText( _x( 'The title of the paywall block', 'Fields', 'fdmg-labels-admin' ) )
								->required(),
							WYSIWYGEditor::make( _x( 'Content inside the paywall block', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_CONTENT_REQUIRED_ROLE )
								->helperText( _x( 'The content of the paywall block. Lists will be displayed as checkmark lists.', 'Fields', 'fdmg-labels-admin' ) )
								->tabs( 'visual' )
								->toolbar( 'basic' )
								->disableMediaUpload()
								->required(),
							Link::make( _x( 'Choose subscription button', 'Fields', 'fdmg-labels-admin' ), self::META_PAYWALL_SUBSCRIPTION_BUTTON )
								->helperText( _x( 'The link to the subscriptions page.', 'Fields', 'fdmg-labels-admin' ) )
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

	/**
	 * @return array<string,mixed>
	 */
	public function get_options(): array {
		return get_field( self::META_PAYWALL, 'options' ) ?? array();
	}
}
