<?php

// Clarkson
( new \Clarkson\CronAuth() )->register_hooks();
( new \Clarkson\Hardening() )->register_hooks();
( new \Clarkson\MailStyling() )->register_hooks();

// Clarkson WPAdmin
( new \Clarkson\WPadmin\Capabilities() )->register_hooks();
( new \Clarkson\WPadmin\Gutenberg() )->register_hooks();
( new \Clarkson\WPadmin\Plugins() )->register_hooks();

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
( new \Clarkson\WPadmin\Darkmode() )->register_hooks();
( new \Clarkson\Theme\DataLayer() )->register_hooks();

// Clarkson Utilities
( new \Clarkson\Utilities\Init() )->register_hooks();

// FDMG Labels
( new \FDMGLabels\API\Init() )->register_hooks();
( new \FDMGLabels\Comments\Comments() )->register_hooks();
( new \FDMGLabels\DownloadMonitor() )->register_hooks();
( new \FDMGLabels\Search\Search() )->register_hooks();
( new \FDMGLabels\Search\Filter() )->register_hooks();
( new \FDMGLabels\Search\ElasticPress() )->register_hooks();
( new \FDMGLabels\Widgets\WidgetAreas() )->register_hooks();
( new \FDMGLabels\GravityForms\Init() )->register_hooks();
( new \FDMGLabels\Logging\Init() )->register_hooks();
( new \FDMGLabels\FeatureManager() )->register_hooks();
( new \FDMGLabels\Auth\Init() )->register_hooks();
( new \FDMGLabels\Paywall\Init() )->register_hooks();
( new \FDMGLabels\StaticPages\Init() )->register_hooks();
( new \FDMGLabels\Connectors\FDMGContentAPI\Init() )->register_hooks();
( new \FDMGLabels\AdminAjax\Init() )->register_hooks();
( new \FDMGLabels\Robots() )->register_hooks();
( new \FDMGLabels\Redirection() )->register_hooks();
( new \FDMGLabels\ServerSideTracking\Init() )->register_hooks();
( new \FDMGLabels\EmbedCacheRemover() )->register_hooks();
( new \FDMGLabels\RSSFeed\MediaItemRSSFeed() )->register_hooks();
( new \FDMGLabels\Author\Metabox() )->register_hooks();
( new \FDMGLabels\Newsletter\FDMGSelligent() )->register_hooks();
( new \FDMGLabels\ActionScheduler\Actions\AdvanceOptinNewsletterActions() )->register_hooks();

// FDMG Labels CLI
( new \FDMGLabels\CLI\Init() )->register_commands();

add_filter(
	'wp', function () {
		if ( ! function_exists( 'gravity_form' ) ) {
			function gravity_form( ...$args ) {
				return '';
			}
		}
		if ( ! function_exists( 'yoast_breadcrumb' ) ) {
			function yoast_breadcrumb( ...$args ) {
				return '';
			}
		}
		if ( ! function_exists( 'gtm4wp_the_gtm_tag' ) ) {
			function gtm4wp_the_gtm_tag( ...$args ) {
				return '';
			}
		}
	}
);
