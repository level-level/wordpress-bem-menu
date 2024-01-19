<?php

use FDMGLabels\Logging\PSR3BaseLoggerAdapter;
use MHCG\Monolog\Handler\WPCLIHandler;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;

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

( new \FDMGLabels\GravityForms\Feeds() )->register_hooks();

if ( function_exists( 'is_blog_installed' ) && is_blog_installed() ) {
	( new \FDMGLabels\ActionScheduler\ActionSchedulerSetup() )->register_hooks();
}
