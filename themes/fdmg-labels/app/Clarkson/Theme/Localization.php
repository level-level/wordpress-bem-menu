<?php

namespace Clarkson\Theme;

use WP_Post_Type;

class Localization {
	protected const SLUGS_DOMAIN = 'fdmg-labels-admin-slugs';

	public function register_hooks(): void {
		add_action( 'after_setup_theme', array( $this, 'load_extra_textdomains' ) );
		add_filter( 'theme_locale', array( $this, 'set_theme_locale_for_slug_translations' ), 1, 2 );
		add_action( 'loco_admin_shutdown', array( $this, 'flush_slugs_after_changes' ) );
	}

	public function load_extra_textdomains(): void {
		load_theme_textdomain( self::SLUGS_DOMAIN );
	}

	public function set_theme_locale_for_slug_translations( string $locale, string $domain ): string {
		if ( self::SLUGS_DOMAIN === $domain ) {
			$locale = get_locale();
		}

		return $locale;
	}

	/**
	 * Flush rewrite rules on loco translate save
	 */
	public function flush_slugs_after_changes(): void {
		global $l10n_unloaded, $l10n;
		if ( filter_input( INPUT_POST, 'action' ) === 'loco_json' && filter_input( INPUT_POST, 'route' ) === 'save' && filter_input( INPUT_POST, 'domain' ) === self::SLUGS_DOMAIN ) {

			if ( is_array( $l10n_unloaded ) ) {
				$l10n_unloaded[ self::SLUGS_DOMAIN ] = true;
				unset( $l10n[ self::SLUGS_DOMAIN ] );
			}

			load_theme_textdomain( self::SLUGS_DOMAIN );

			$post_types = new PostTypes();
			$post_types->register_post_types();

			$this->update_mlp_post_type_slugs();

			flush_rewrite_rules( true );
		}
	}

	/**
	 * Update MLP cpt slug setting to translated slug via loco translate
	 */
	public function update_mlp_post_type_slugs(): void {
		if ( ! class_exists( 'Inpsyde\MultilingualPress\Core\Admin\PostTypeSlugsSettingsRepository' ) ) {
			return;
		}

		$slugs            = array();
		$slugs_repository = new \Inpsyde\MultilingualPress\Core\Admin\PostTypeSlugsSettingsRepository();
		$post_types       = get_post_types(
			array(
				'show_ui' => true,
			),
			'objects'
		);

		foreach ( $post_types as $post_type ) {
			if ( $post_type instanceof WP_Post_Type ) {
				if ( $post_type->rewrite ) {
					$slugs[ $post_type->name ] = $post_type->rewrite['slug'];
				}
			}
		}

		$slugs_repository->updatePostTypeSlugs( $slugs );
	}
}
