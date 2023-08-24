<?php

// Clarkson
( new \Clarkson\CronAuth() )->register_hooks();
( new \Clarkson\Hardening() )->register_hooks();
( new \Clarkson\MailStyling() )->register_hooks();
( new \Clarkson\Storybook() )->register_hooks();

// Clarkson WPAdmin
( new \Clarkson\WPadmin\Capabilities() )->register_hooks();
( new \Clarkson\WPadmin\Gutenberg() )->register_hooks();
( new \Clarkson\WPadmin\Plugins() )->register_hooks();
( new \Clarkson\WPadmin\Tools\Base() )->register_hooks();
( new \Clarkson\WPadmin\Tools\Styleguide() )->register_hooks();

// Clarkson Theme
( new \Clarkson\Theme\Assets() )->register_hooks();
( new \Clarkson\Theme\ACFBlocks() )->register_hooks();
( new \Clarkson\Theme\ACFWidgets() )->register_hooks();
( new \Clarkson\Theme\ACFPages() )->register_hooks();
( new \Clarkson\Theme\ACF() )->register_hooks();
( new \Clarkson\Theme\Blocks() )->register_hooks();
( new \Clarkson\Theme\Cleanup() )->register_hooks();
( new \Clarkson\Theme\Localization() )->register_hooks();
( new \Clarkson\Theme\Navigation\Navigation() )->register_hooks();
( new \Clarkson\Theme\OEmbed() )->register_hooks();
( new \Clarkson\Theme\PostTypes() )->register_hooks();
( new \Clarkson\Theme\Rewrites() )->register_hooks();
( new \Clarkson\Theme\Support() )->register_hooks();
( new \Clarkson\Theme\Taxonomies() )->register_hooks();
( new \Clarkson\Theme\Options() )->register_hooks();
( new \Clarkson\Theme\ImageSizes() )->register_hooks();
( new \Clarkson\Theme\SVGIcons() )->register_hooks();
( new \Clarkson\Theme\WPSEO() )->register_hooks();
( new \Clarkson\Theme\LoginLogo() )->register_hooks();

// Clarkson Utilities
( new \Clarkson\Utilities\Init() )->register_hooks();

// FDMG Labels
( new \FDMGLabels\Comments\Comments() )->register_hooks();
( new \FDMGLabels\Search\Search() )->register_hooks();
( new \FDMGLabels\Widgets\WidgetAreas() )->register_hooks();
( new \FDMGLabels\GravityForms\Init() )->register_hooks();
( new \FDMGLabels\Logging\Init() )->register_hooks();
( new \FDMGLabels\Newsletter\Init() )->register_hooks();
( new \FDMGLabels\FeatureManager() )->register_hooks();
( new \FDMGLabels\Paywall\Init() )->register_hooks();

// FDMG Labels CLI
( new \FDMGLabels\CLI\Init() )->register_commands();
