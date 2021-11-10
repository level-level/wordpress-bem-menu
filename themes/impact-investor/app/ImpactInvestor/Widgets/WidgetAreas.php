<?php

namespace ImpactInvestor\Widgets;

class WidgetAreas {

	public function register_hooks(): void {
		add_action( 'widgets_init', array( $this, 'register_widget_areas' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	public function register_widget_areas(): void {
		register_sidebar(
			array(
				'name'          => _x( 'Page header advertisement area', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-header-ad',
				'description'   => _x( 'Add a single advertisement block to appear on the top of all pages.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Homepage', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-homepage',
				'description'   => _x( 'Add widgets to appear on the homepage.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Article', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-article',
				'description'   => _x( 'Add widgets to appear on article pages.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Page', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-page',
				'description'   => _x( 'Add widgets to appear on the content pages.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Author', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-author',
				'description'   => _x( 'Add widgets to appear on the author pages.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Partner', 'Widgets', 'impact-investor' ),
				'id'            => 'widget-area-partner',
				'description'   => _x( 'Add widgets to appear on the partner pages.', 'Widgets', 'impact-investor' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);
	}

	public function get_widgetarea( string $widget ): string {
		ob_start();
		dynamic_sidebar( $widget );
		$sidebar = ob_get_contents();
		ob_end_clean();
		return $sidebar;
	}

	public function add_context_args( array $context ): array {
		$context['sidebar'] = $this;
		return $context;
	}
}
