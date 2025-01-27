<?php
namespace Clarkson\Theme\ACFPages;

use Clarkson\Theme\PostTypes\Vacancy;
use Extended\ACF\Fields\Group;
use Extended\ACF\Fields\Text;
use Extended\ACF\Fields\WYSIWYGEditor;
use Extended\ACF\Location;

class VacancyOptions {
	public const POST_TYPE = Vacancy::POST_TYPE_NAME;
	public const MENU_SLUG = 'acf-vacancy-archive-options';

	protected const FIELD_GROUP        = 'options_vacancy_options';
	public const POST_TYPE_GROUP       = 'ii_vacancy_options';
	public const POST_TYPE_GROUP_TITLE = 'title';
	public const POST_TYPE_GROUP_INTRO = 'intro';

	public function register_hooks(): void {
		add_action( 'acf/init', array( $this, 'register' ) );
		add_action( 'acf/init', array( $this, 'register_fields' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
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
				'title'     => _x( 'Settings', 'Options', 'fdmg-labels-admin' ),
				'parent'    => 'edit.php?post_type=' . self::POST_TYPE,
				'menu_slug' => self::MENU_SLUG,
			)
		);
	}

	public function register_fields(): void {
		register_extended_field_group(
			array(
				'key'      => self::FIELD_GROUP,
				'title'    => _x( 'Vacancy archive options', 'Fields', 'fdmg-labels-admin' ),
				'fields'   => array(
					Group::make( _x( 'Vacancy archive options', 'Fields', 'fdmg-labels-admin' ), self::POST_TYPE_GROUP )
						->fields(
							array(
								Text::make( _x( 'Title', 'Fields', 'fdmg-labels-admin' ), self::POST_TYPE_GROUP_TITLE ),
								WYSIWYGEditor::make( _x( 'Introduction text', 'Fields', 'fdmg-labels-admin' ), self::POST_TYPE_GROUP_INTRO )
									->disableMediaUpload()
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
