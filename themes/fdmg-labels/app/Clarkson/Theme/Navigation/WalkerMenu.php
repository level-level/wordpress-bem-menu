<?php
namespace Clarkson\Theme\Navigation;

use Clarkson\Theme\SVGIcons;
use Clarkson_Core\Templates;
use stdClass;
use Walker_Nav_Menu;
use WP_Post;

class WalkerMenu extends Walker_Nav_Menu {

	public string $css_class_prefix;
	/**
	 * @var array<string,string>
	 */
	public array $item_css_class_suffixes;
	public bool $show_icons;

	public function __construct( string $css_class_prefix, bool $show_icons ) {
		$this->has_children     = false;
		$this->css_class_prefix = $css_class_prefix;
		$this->show_icons       = $show_icons;

		$this->item_css_class_suffixes = array(
			'item'                    => '__item',
			'parent_item'             => '__item--parent',
			'active_item'             => '__item--active',
			'parent_of_active_item'   => '__item--parent-active',
			'ancestor_of_active_item' => '__item--ancestor-active',
			'sub_menu'                => '-sub',
			'sub_menu_item'           => '-sub__item',
			'link'                    => '__link',
		);
	}

	/**
	 * Starts the list before the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass|array<mixed> $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ): void {
		$classes = array(
			'sub-menu',
			$this->css_class_prefix . $this->item_css_class_suffixes['sub_menu'],
		);

		$class_names = implode( ' ', $classes );

		$output .= "<ul class='{$class_names}' aria-hidden='true'>";
	}

	/**
	 * Ends the list of after the elements are added.
	 *
	 * @param string   $output Used to append additional content (passed by reference).
	 * @param int      $depth  Depth of menu item. Used for padding.
	 * @param stdClass|array<mixed> $args   An object of wp_nav_menu() arguments.
	 * @return void
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ): void {
		$output .= '</ul>';
	}

	/**
	 * Starts the element output.
	 *
	 * @param string   $output            Used to append additional content (passed by reference).
	 * @param WP_Post  $data_object       Menu item data object.
	 * @param int      $depth             Depth of menu item. Used for padding.
	 * @param stdClass|array<mixed> $args        An object of wp_nav_menu() arguments.
	 * @param int      $current_object_id Optional. ID of the current menu item. Default 0.
	 * @return void
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ): void {
		$menu_item  = $data_object;
		$args_array = is_object( $args ) ? get_object_vars( $args ) : $args;
		$prefix     = $this->css_class_prefix;
		$suffix     = $this->item_css_class_suffixes;
		$icon       = get_field( Navigation::NAVIGATION_META_ICON, $menu_item );

		$has_children = isset( $args_array['walker'] ) && $args_array['walker']->has_children;
		$has_submenu  = false;

		if ( $has_children && ( $args_array['depth'] === 0 || $args_array['depth'] > $depth + 1 ) ) {
			$has_submenu = true;
		}

		$class_names = implode( ' ', $this->get_bem_classes( $menu_item, $has_submenu, $depth ) );

		$output .= "<li class='{$class_names}'>";

		$link_classes = array(
			'item_link'   => 0 === $depth ? $prefix . $suffix['link'] : '',
			'depth_class' => $depth >= 1 ? $prefix . $suffix['sub_menu'] . $suffix['link'] : '',
		);

		$link_classes = implode( ' ', $link_classes );

		$attributes  = $this->get_link_attributes( $menu_item );
		$item_output = "<a {$attributes} class='{$link_classes}'>";

		if ( $icon && $this->show_icons ) {
			$link_classes = trim( $link_classes );
			$icon_class   = "{$link_classes}-icon icon-inline icon-$icon";
			$item_output .= "<span class='$icon_class' aria-hidden='true'>" . SVGIcons::get_instance()->get( $icon ) . '</span>';
		}
		$item_output .= "{$menu_item->title}</a>";

		if ( $has_submenu ) {
			$template     = Templates::get_instance();
			$item_output .= $template->render_twig(
				'styleguide/components/buttons/button-element.twig',
				array(
					'class'                 => $this->css_class_prefix . '__toggle',
					'aria_label'            => sprintf( _x( 'Submenu %s', 'Navigation', 'fdmg-labels' ), $menu_item->title ),
					'aria_expanded_support' => true,
					'icon'                  => 'chevron-down',
					'svg_icons'             => SVGIcons::get_instance(),
				),
				true
			);
		}

		$output .= $item_output;
	}

	/**
	 * Ends the element output, if needed.
	 */
	/**
	 *
	 * @param string $output
	 * @param object $data_object
	 * @param integer $depth
	 * @param object $args
	 * @return void
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ): void {
		$output .= '</li>';
	}

	/**
	 * Returns the correct bem classes for the menu item.
	 *
	 * @param WP_Post $item
	 * @param boolean $has_submenu
	 * @param integer $depth
	 * @return array<string,mixed>
	 */
	protected function get_bem_classes( WP_Post $item, bool $has_submenu, int $depth ): array {
		global $post;
		$prefix = $this->css_class_prefix;
		$suffix = $this->item_css_class_suffixes;

		$parent_class          = $has_submenu ? $prefix . $suffix['parent_item'] : '';
		$active_page_class     = '';
		$active_parent_class   = '';
		$active_ancestor_class = '';

		if ( is_array( $item->classes ) ) {
			// Checks if a single (custom) post is active and add an active parent class to the archive link.
			$active_post_type      = ! is_archive() && $post ? in_array( 'menu-item-object-' . $post->post_type, $item->classes, true ) && in_array( 'menu-item-type-post_type_archive', $item->classes, true ) : false;
			$active_posts_page     = ! is_home() && $post && $post->post_type === 'post' ? in_array( 'current_page_parent', $item->classes, true ) : false;
			$active_page_class     = in_array( 'current-menu-item', $item->classes, true ) ? $prefix . $suffix['active_item'] : '';
			$active_parent_class   = in_array( 'current-menu-parent', $item->classes, true ) || $active_posts_page || $active_post_type ? $prefix . $suffix['parent_of_active_item'] : '';
			$active_ancestor_class = in_array( 'current-menu-ancestor', $item->classes, true ) || in_array( 'current-page-ancestor', $item->classes, true ) ? $prefix . $suffix['ancestor_of_active_item'] : '';
		}

		return array_filter(
			array(
				'item_class'            => 0 === $depth ? $prefix . $suffix['item'] : '',
				'parent_class'          => $parent_class,
				'active_page_class'     => $active_page_class,
				'active_parent_class'   => $active_parent_class,
				'active_ancestor_class' => $active_ancestor_class,
				'depth_class'           => $depth >= 1 ? $prefix . $suffix['sub_menu_item'] : '',
			)
		);
	}

	/**
	 * Returns the correct link attributes for the menu item.
	 *
	 * @param WP_Post $menu_item
	 * @return string
	 */
	protected function get_link_attributes( WP_Post $menu_item ): string {
		$atts           = array();
		$atts['title']  = ! empty( $menu_item->attr_title ) ? $menu_item->attr_title : '';
		$atts['target'] = ! empty( $menu_item->target ) ? $menu_item->target : '';
		if ( '_blank' === $menu_item->target && empty( $menu_item->xfn ) ) {
			$atts['rel'] = 'noopener';
		} else {
			$atts['rel'] = $menu_item->xfn;
		}
		if ( '_blank' === $menu_item->target ) {
			$atts['aria-label'] = sprintf( _x( '%s - opens in a new window', 'Navigation', 'fdmg-labels' ), $menu_item->title );
		}
		$atts['href'] = ! empty( $menu_item->url ) ? $menu_item->url : '';

		if ( $menu_item->current ) {
			$atts['aria-current'] = 'page';
		}

		if ( $menu_item->current_item_ancestor ) {
			$atts['aria-current'] = 'true';
		}

		$attributes = '';
		foreach ( $atts as $attr => $value ) {
			if ( is_scalar( $value ) && '' !== $value && false !== $value ) {
				$value       = ( 'href' === $attr ) ? esc_url( (string) $value ) : esc_attr( (string) $value );
				$attributes .= ' ' . $attr . '="' . $value . '"';
			}
		}

		return $attributes;
	}
}
