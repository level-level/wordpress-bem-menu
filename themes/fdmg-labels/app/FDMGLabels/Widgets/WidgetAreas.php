<?php

namespace FDMGLabels\Widgets;

use FDMGLabels\FeatureManager;

class WidgetAreas {

	public function register_hooks(): void {
		add_action( 'widgets_init', array( $this, 'register_widget_areas' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	public function register_widget_areas(): void {

		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_COMPACT_HEADER ) ) {
			register_sidebar(
				array(
					'name'          => _x( 'Navigation advertisement area', 'Widgets', 'fdmg-labels-admin' ),
					'id'            => 'widget-area-navigation-ad',
					'description'   => _x( 'Add a single advertisement block to appear above the navigation.', 'Widgets', 'fdmg-labels-admin' ),
					'before_widget' => '<div class="section">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2 class="widget-title">',
					'after_title'   => '</h2>',
				)
			);
		}

		register_sidebar(
			array(
				'name'          => _x( 'Page header advertisement area', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-header-ad',
				'description'   => _x( 'Add a single advertisement block to appear on the top of all pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Homepage', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-homepage',
				'description'   => _x( 'Add widgets to appear on the homepage.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Article', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-article',
				'description'   => _x( 'Add widgets to appear on article pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Page', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-page',
				'description'   => _x( 'Add widgets to appear on the content pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Author', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-author',
				'description'   => _x( 'Add widgets to appear on the author pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Partner', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-partner',
				'description'   => _x( 'Add widgets to appear on the partner pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Vacancy', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-vacancy',
				'description'   => _x( 'Add widgets to appear on the vacancy pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Category', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-category',
				'description'   => _x( 'Add widgets to appear on the category pages.', 'Widgets', 'fdmg-labels-admin' ),
				'before_widget' => '<div class="section">',
				'after_widget'  => '</div>',
				'before_title'  => '<h2 class="widget-title">',
				'after_title'   => '</h2>',
			)
		);

		register_sidebar(
			array(
				'name'          => _x( 'Search results', 'Widgets', 'fdmg-labels-admin' ),
				'id'            => 'widget-area-search-results',
				'description'   => _x( 'Add widgets to appear on the search results page.', 'Widgets', 'fdmg-labels-admin' ),
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
