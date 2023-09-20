<?php
namespace Clarkson\Theme;

class SVGIcons {

	/**
	 * @var SVGIcons|null
	 */
	private static $instance;

	/**
	 * @var array
	 */
	public $svgs = array();

	public static function get_instance(): SVGIcons {
		if ( ! self::$instance instanceof SVGIcons ) {
			self::$instance = new SVGIcons();
		}

		return self::$instance;
	}

	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	public function add_context_args( array $context ): array {
		$context['svg_icons'] = self::get_instance();
		return $context;
	}

	/**
	 * Get svg icon HTML by svg name
	 */
	public function get( string $name ): string {
		if ( empty( $this->svgs ) ) {
			$this->svgs = $this->get_svgs();
		}

		return $this->svgs[ $name ] ?? '';
	}

	/**
	 * Get all svg icons from images/icons
	 */
	public function get_svgs(): array {
		$path  = get_stylesheet_directory() . '/src/images/icons';
		$icons = scandir( $path );
		$svgs  = array();

		foreach ( $icons as $file_name ) {
			if ( ( substr( strtolower( $file_name ), -strlen( '.svg' ) ) === '.svg' ) ) {
				$file_path = $path . '/' . $file_name;

				if ( file_exists( $file_path ) ) {
					$icon_name          = pathinfo( $file_name, PATHINFO_FILENAME );
					$svgs[ $icon_name ] = file_get_contents( $file_path ) ?: ''; // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				}
			}
		}

		return $svgs;
	}

	/**
	 * Creates an icon array with html including <svg> icon and icon name.
	 * By default shows all icons, it's optional to include only selected icons
	 * or exclude the selected icons.
	 *
	 * @param array<string> $selected_icons
	 * @param boolean $exclude_icons
	 * @return array<string>
	 */
	public function get_icon_list( array $selected_icons = array(), bool $exclude_icons = false ): array {
		if ( empty( $this->svgs ) ) {
			$this->svgs = $this->get_svgs();
		}

		$svgs = $this->svgs;

		if ( ! empty( $selected_icons ) ) {
			if ( ! $exclude_icons ) {
				$svgs = array();
			}

			foreach ( $selected_icons as $icon_name ) {
				if ( $exclude_icons ) {
					unset( $svgs[ $icon_name ] );
				} else {
					$svgs[ $icon_name ] = $this->get( $icon_name );
				}
			}
		}

		$choices = array();
		foreach ( $svgs as $icon_name => $svg ) {
			$formatted_name = ucwords( str_replace( '-', ' ', $icon_name ) );

			$choices[ $icon_name ] = "
				<span style='display:flex; gap:10px; align-items:center'>
					<span aria-hidden='true' style='display:flex;'>{$svg}</span>{$formatted_name}
				</span>
			";
		}

		return $choices;
	}
}
