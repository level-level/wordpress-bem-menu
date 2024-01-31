<?php
namespace Clarkson\Theme;

use FDMGLabels\AdminAjax\Init as AdminAjaxInit;
use FDMGLabels\API\Init as APIInit;
use LevelLevel\Logging\Settings\SentryFrontend\Fields\DSN;

class Assets {

	/**
	 * @var mixed $version
	 */
	private $version = false;

	public function register_hooks(): void {
		$this->set_theme_version();

		add_action( 'wp_print_styles', array( $this, 'dequeue' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin' ) );
	}

	public function set_theme_version(): void {
		$theme = wp_get_theme();

		if ( $theme->exists() ) {
			$this->version = $theme->get( 'Version' );
		}
	}

	/**
	 * By using Webpack HMR the dist folder is being served from the memory, therefor not visible
	 */
	public function is_development_mode(): bool {
		return ! file_exists( get_stylesheet_directory() . '/dist/main.js' );
	}

	public function dequeue(): void {
	}

	public function enqueue_admin(): void {
		if ( $this->is_development_mode() ) {
			$this->enqueue_development( 'clarkson_admin', array(), '/src/admin.js', true );
		} else {
			wp_enqueue_script( 'clarkson_admin', get_template_directory_uri() . '/dist/admin.js', array(), $this->version, true );
		}
	}

	public function enqueue(): void {
		if ( $this->is_development_mode() ) {
			$this->enqueue_development( 'clarkson_main', array( 'jquery' ), '/src/main.js', true );
		} else {
			$this->enqueue_production();
		}

		( new ScriptTranslations() )->register_hooks();
		( new APIInit() )->add_script_variables();
	}

	public function enqueue_editor(): void {
		$deps = array( 'wp-blocks', 'wp-dom-ready', 'wp-edit-post' );

		if ( $this->is_development_mode() ) {
			$this->enqueue_development( 'clarkson_editor', $deps, '/src/editor.js', true );
		} else {
			$this->enqueue_editor_production( $deps );
		}

		( new AdminAjaxInit() )->localize_editor();
	}

	public function enqueue_development( string $handle, array $deps, string $path, bool $in_footer ): void {
		$src = $this->get_development_src( $path );
		wp_enqueue_script( $handle, $src, $deps, $this->version, $in_footer );
	}

	public function enqueue_production(): void {
		$sentry_dsn = ( new DSN() )->get_value();

		if ( $sentry_dsn ) {
			wp_enqueue_script( 'clarkson_sentry', get_template_directory_uri() . '/dist/sentry.js', array(), $this->version, false );
		}

		wp_enqueue_script( 'clarkson_main', get_template_directory_uri() . '/dist/main.js', array( 'jquery' ), $this->version, true );
		wp_enqueue_style( 'clarkson_main', get_template_directory_uri() . '/dist/main.css', array(), $this->version, 'all' );
	}

	public function enqueue_editor_production( array $deps ): void {
		wp_enqueue_script( 'clarkson_editor', get_stylesheet_directory_uri() . '/dist/editor.js', $deps, $this->version, true );
		wp_enqueue_style( 'clarkson_editor', get_stylesheet_directory_uri() . '/dist/editor.css', array( 'wp-edit-blocks' ), $this->version );
	}

	/**
	 * Get config settings from config.local.json
	 */
	public function get_src_config(): array {
		$local_config_path = get_stylesheet_directory() . '/config.local.json';
		$local_config      = array();

		if ( file_exists( $local_config_path ) ) {
			$local_config = json_decode( file_get_contents( $local_config_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		}

		return $local_config;
	}

	/**
	 * Get the stylesheet directory uri based on your current environment
	 */
	public function get_assets_directory_url(): string {
		if ( $this->is_development_mode() ) {
			return $this->get_development_src( '/src' );
		}
		return get_stylesheet_directory_uri() . '/dist';
	}

	public function add_context_args( array $context ): array {
		$context['assets'] = $this;
		return $context;
	}

	public function get_development_src( string $path = '' ): string {
		$config   = $this->get_src_config();
		$secure   = $config['secure'] ?? false;
		$port     = $config['port'] ?? 1234;
		$protocol = $secure ? 'https' : 'http';
		return $protocol . '://localhost:' . $port . $path;
	}
}
