<?php

/**
 * This functions file is directly used by WP All Export and provides custom import functions.
 *
 * All function names defined in this file should be prefixed with `fdmg_wpai_`.
 */

use FDMGLabels\Import\Helpers\BinaryFiles;
use FDMGLabels\Import\Helpers\ContentParser;
use FDMGLabels\Import\Helpers\ImportedContentFinder;
use FDMGLabels\Import\Helpers\Pictures;
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

function fdmg_wpai_get_post_status( string $state ): string {
	return ( new Utilities() )->get_post_status( $state );
}

function fdmg_wpai_get_post_id_by_old_id( string $prefix, ?string $old_id ): int {
	if ( empty( $old_id ) ) {
		return 0;
	}
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

function fdmg_format_value( string $value, string $default_string = '' ): string {
	return ( new ContentParser() )->format_value( $value, $default_string );
}

function fdmg_wpai_picture_get_title( string $title, string $file_path ): string {
	return ( new Pictures() )->get_title( $title, $file_path );
}

function fdmg_wpai_picture_get_wp_attached_file( string $identifier, string $file_path ): string {
	return ( new Pictures() )->get_wp_attached_file( (int) $identifier, $file_path );
}

function fdmg_wpai_picture_get_attachment_metadata( string $identifier, string $file_path ): string {
	return ( new Pictures() )->get_attachment_metadata( (int) $identifier, $file_path );
}

function fdmg_wpai_binary_file_get_title( string $title, string $file_path ): string {
	return ( new BinaryFiles() )->get_title( $title, $file_path );
}

function fdmg_wpai_binary_file_get_wp_attached_file( string $identifier, string $file_path ): string {
	return ( new BinaryFiles() )->get_wp_attached_file( (int) $identifier, $file_path );
}

function fdmg_wpai_parse_article( ?string $content, ?string $is_free, ?string $intro ): string {
	if ( empty( $content ) ) {
		$content = '';
	}

	if ( empty( $intro ) ) {
		$intro = '';
	}

	return ( new ContentParser() )->parse_article( $content, (bool) $is_free, $intro );
}

function fdmg_wpai_get_author_ids_by_old_ids( ?string $old_ids ): string {
	if ( empty( $old_ids ) ) {
		return '';
	}
	return ( new ImportedContentFinder() )->get_author_ids_by_old_ids( $old_ids );
}

function fdmg_wpai_get_categories_by_old_ids( ?string $old_ids ): string {
	if ( empty( $old_ids ) ) {
		return '';
	}
	return ( new ImportedContentFinder() )->get_categories_from_old_ids( $old_ids );
}

function fdmg_wpai_get_section_by_old_id( ?string $old_id ): string {
	if ( empty( $old_id ) ) {
		return '';
	}
	return ( new ImportedContentFinder() )->get_section_from_old_id( $old_id );
}

function fdmg_wpai_get_slug_from_url( ?string $url ): string {
	if ( ! $url ) {
		return '';
	}

	$parts = explode( '/', $url );
	return (string) array_pop( $parts );
}
