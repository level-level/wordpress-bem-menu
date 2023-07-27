<?php

$logging = new \LevelLevel\Logging\Log();
$logging->init();

( new \FDMGLabels\GravityForms\Feeds() )->register_hooks();
