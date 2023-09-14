<?php

$logging = new \LevelLevel\Logging\Log();
$logging->init();

( new \FDMGLabels\GravityForms\Feeds() )->register_hooks();

if ( function_exists( 'is_blog_installed' ) && is_blog_installed() ) {
	( new \FDMGLabels\ActionScheduler\ActionSchedulerSetup() )->register_hooks();
}
