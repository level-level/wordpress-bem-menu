<?php
namespace Clarkson\Theme\ACFWidgets;

use Extended\ACF\Fields\Select;
use Extended\ACF\Fields\Text;
use Extended\ACF\Location;

class Menu {
	public const BLOCK_NAME = 'menu';

	protected const FIELD_GROUP = 'widget_menu';

	public const BLOCK_META_TITLE = 'title';
	public const BLOCK_META_MENU  = 'menu';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
	}

	public function register(): void {
		acf_register_block_type(
			array(
				'name'        => 'widget-' . self::BLOCK_NAME,
				'title'       => _x( 'Navigation menu', 'Block', 'fdmg-labels-admin' ),
				'description' => _x( 'Displays a navigation menu. Note: this widget is only attended to use inside the Menu widget area.', 'Block', 'fdmg-labels-admin' ),
				'icon'        => 'menu-alt3',
				'align'       => '', // full, wide, left, center, right
				'keywords'    => array(
					_x( 'navigation', 'Keyword', 'fdmg-labels-admin' ),
					_x( 'menu', 'Keyword', 'fdmg-labels-admin' ),
				),
				'supports'    => array(
					'align'    => false,
					'multiple' => true,
				),
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Navigation menu', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Text::make( _x( 'Title', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_TITLE )
						->required(),

					Select::make( _x( 'Select a menu', 'Fields', 'fdmg-labels-admin' ), self::BLOCK_META_MENU )
						->choices( $this->get_nav_menus() )
						->nullable()
						->stylized()
						->lazyLoad()
						->required(),
				),
				'location' => array(
					Location::where( 'block', 'acf/widget-' . self::BLOCK_NAME ),
				),
			)
		);
	}

	/**
	 * @return array<string,string>
	 */
	private function get_nav_menus(): array {
		$menus   = wp_get_nav_menus();
		$choices = array();

		if ( ! empty( $menus ) ) {
			foreach ( $menus as $menu ) {
				$choices[ $menu->slug ] = $menu->name;
			}
		}

		return $choices;
	}
}
