<?php
namespace Clarkson\Theme\ACFPages;

use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Select;
use Extended\ACF\Location;

class SiteSettings {

	protected const FIELD_GROUP = 'site_settings';

	public const MENU_SLUG = 'acf-site-options';

	public const OPTION_GROUP        = 'site_settings';
	public const OPTION_ACTIVE_THEME = 'active_theme';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'clarkson_context_args', array( $this, 'add_context_args' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['site'] = get_field( self::OPTION_GROUP, 'options' ) ?: array();
		return $context;
	}

	public function register(): void {
		acf_add_options_page(
			array(
				'menu_title' => _x( 'Site settings', 'Options', 'fdmg-labels-admin' ),
				'page_title' => _x( 'Site settings', 'Options', 'fdmg-labels-admin' ),
				'menu_slug'  => self::MENU_SLUG,
				'icon_url'   => 'dashicons-admin-generic',
				'redirect'   => false,
				'capability' => 'manage_options',
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Site settings', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Site settings', 'Fields', 'fdmg-labels-admin' ), self::OPTION_GROUP )
						->fields(
							array(
								Select::make( _x( 'Select active theme', 'Fields', 'fdmg-labels-admin' ), self::OPTION_ACTIVE_THEME )
									->choices(
										array(
											'impact-investor' => 'Impact Investor',
											'energeia'     => 'Energeia',
											'pensioen-pro' => 'Pensioen Pro',
										),
									)
									->required()
									->defaultValue( 'impact-investor' ),
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
