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
	 * Make svg icons available in JS
	 */
	public function localize_script_icons(): void {
		wp_localize_script(
			'clarkson_main',
			'icons',
			array(
				'arrowDown' => $this->get( 'carret-down' ),
			)
		);
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
}
