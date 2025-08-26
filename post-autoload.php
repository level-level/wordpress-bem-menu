<?php

use FDMGLabels\Logging\PSR3BaseLoggerAdapter;
use LevelLevel\ActionScheduler\ActionSchedulerSetup;
use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Monolog\Registry;

// Logging
add_filter(
	'level_level_log_handlers', function ( array $handlers ) {
		$handlers['fdmg_logger'] = new PsrHandler( new PSR3BaseLoggerAdapter(), Logger::INFO );

		if ( class_exists( WP_CLI::class, false ) && defined( 'WP_CLI' ) && (bool) constant( 'WP_CLI' ) ) {
			$handlers['wp_cli_handler'] = new WPCLIHandler();
		}
		return $handlers;
	}
);
$logging = new \LevelLevel\Logging\Log();
$logging->init();

// Action Scheduler
if ( function_exists( 'is_blog_installed' ) && is_blog_installed() ) {
	$action_scheduler_setup = new ActionSchedulerSetup();
	$action_scheduler_setup->setLogger( Registry::wordpress() ); // Adding the logger
	$action_scheduler_setup->register_hooks();
}

/**
 * This solves a conflict between Yoast and KokoAnalytics.
 * Yoast uses the shutdown hook to perform some stuff, using WP functions.
 * KokoAnalytics responds directly on load to a wp-ajax call, without the proper hook.
 * After responding to the hook, KokoAnalytics exits it' script, triggering the shutdown hook.
 * Because the KokoAnalytics plugin is loaded before the pluggable functions, the code of Yoast crashes when it calls the get_userdata() function, which does not exist at that point.
 *
 * This workaround makes sure that the pluggable functions are loaded on shutdown.
 */
add_action(
	'shutdown', function (): void {
		require_once ABSPATH . 'wp-includes/pluggable.php';
	}
);
