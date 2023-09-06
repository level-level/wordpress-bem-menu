<?php
namespace Gutenberg\Blocks;

use Clarkson\Theme\Navigation\WalkerMenu;

class acf_widget_menu extends base_block {
	public function get_menu( string $menu_slug, string $menu_class, int $depth = 0 ): string {

		if ( ! is_nav_menu( $menu_slug ) ) {
			return '';
		}

		$args = array(
			'menu'       => $menu_slug,
			'menu_class' => $menu_class,
			'echo'       => false,
			'walker'     => new WalkerMenu( $menu_class, false ),
			'depth'      => $depth,
			'items_wrap' => '<ul class="%2$s">%3$s</ul>',
		);

		/** @var string|false */
		$menu = wp_nav_menu( $args );
		return $menu ?: '';
	}
}
