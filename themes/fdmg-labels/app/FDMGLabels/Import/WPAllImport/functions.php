<?php

/**
 * This functions file is directly used by WP All Export and provides custom import functions.
 *
 * All function names defined in this file should be prefixed with `fdmg_wpai_`.
 */

use FDMGLabels\Import\Helpers\ContentParser;
use FDMGLabels\Import\Helpers\ImportedContentFinder;
use FDMGLabels\Import\Helpers\Utilities;
use FDMGLabels\Import\Init;

/**
 * Require the helper files just to be sure.
 * These files might not be autoloaded automatically, if it's not called from anywhere else in the project (because of Composer PSR-4 autoloading).
 * Uses get_template_directory() to determine the base dir to load the file from.
 */

require_once get_template_directory() . '/app/FDMGLabels/Import/Helpers/Utilities.php';
require_once get_template_directory() . '/app/FDMGLabels/Import/Helpers/ImportedContentFinder.php';

/**
 * Define all custom functions below this comment.
 * All function names defined in this file should be prefixed with `fdmg_wpai_`.
 * If a function contains logic that could be useful in other exports, please create that logic in \FDMGLabels\Impport\Helpers classes.
 * You may then create a simple prefixed function below, which calls the function in the helper class.
 */

function fdmg_wpai_get_old_id_meta_key(): string {
	return Init::META_OLD_ID;
}

function fdmg_wpai_get_development_safe_email( string $email ): string {
	return ( new Utilities() )->get_development_safe_email( $email );
}

function fdmg_wpai_get_post_id_by_old_id( string $prefix, string $old_id ): int {
	return ( new ImportedContentFinder() )->get_post_id_from_old_id( $prefix . $old_id );
}

function fdmg_wpai_get_user_id_by_old_id( string $prefix, string $old_id ): int {
	return ( new ImportedContentFinder() )->get_user_id_from_old_id( $prefix . $old_id );
}

function fdmg_wpai_get_term_id_by_old_id( string $prefix, string $old_id ): int {
	return ( new ImportedContentFinder() )->get_term_id_from_old_id( $prefix . $old_id );
}

function fdmg_wpai_get_comment_id_by_old_id( string $prefix, string $old_id ): int {
	return ( new ImportedContentFinder() )->get_comment_id_from_old_id( $prefix . $old_id );
}

function fdmg_format_value( string $value, string $default = '' ): string {
	return ( new ContentParser() )->format_value( $value, $default );
}
