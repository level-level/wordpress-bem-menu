<?php
namespace Clarkson\Theme\ACFPages;

use Clarkson\Theme\PostTypes\Podcast;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\WysiwygEditor;
use Extended\ACF\Location;
use FDMGLabels\FeatureManager;

class PodcastOptions {
	public const POST_TYPE = Podcast::POST_TYPE_NAME;
	public const MENU_SLUG = 'acf-podcast-archive-options';

	protected const FIELD_GROUP        = 'options_podcast_options';
	public const POST_TYPE_GROUP       = 'podcast_options';
	public const POST_TYPE_GROUP_TITLE = 'title';
	public const POST_TYPE_GROUP_INTRO = 'intro';

	public function register_hooks(): void {
		if ( ( new \FDMGLabels\FeatureManager() )->is_enabled( FeatureManager::FEATURE_ENABLE_PODCAST_POST_TYPE ) ) {
			add_action( 'acf/init', array( $this, 'register' ) );
			add_action( 'acf/init', array( $this, 'register_fields' ) );
			add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		}
	}

	/**
	 *
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		if ( is_post_type_archive( self::POST_TYPE ) ) {
			$context['archive'] = get_field( self::POST_TYPE_GROUP, 'options' ) ?: array();
		}

		return $context;
	}

	public function register(): void {
		acf_add_options_sub_page(
			array(
				'title'     => _x( 'Podcast settings', 'Options', 'fdmg-labels-admin' ),
				'parent'    => 'edit.php?post_type=' . self::POST_TYPE,
				'menu_slug' => self::MENU_SLUG,
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Podcast settings', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Podcast settings', 'Fields', 'fdmg-labels-admin' ), self::POST_TYPE_GROUP )
						->fields(
							array(
								WysiwygEditor::make( _x( 'Introduction text', 'Fields', 'fdmg-labels-admin' ), self::POST_TYPE_GROUP_INTRO )
									->mediaUpload( false )
									->tabs( 'visual' )
									->toolbar( 'simple' ),
							),
						),
				),
				'location' => array(
					Location::where( 'options_page', self::MENU_SLUG ),
				),
			)
		);
	}
}
