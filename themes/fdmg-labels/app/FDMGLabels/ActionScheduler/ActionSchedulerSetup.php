<?php

namespace LevelLevel\FDMGSelligent\ActionScheduler;

class ActionSchedulerSetup {

	public const GROUP = 'fdmg_selligent';

	public function register_hooks(): void {

		// The hook must occur before 'plugins_loaded', or you can use 'plugins_loaded' with negative priority, like -10.
		// see: https://actionscheduler.org/usage/#load-order
		add_action( 'plugins_loaded', array( $this, 'require_plugin_file' ), -10 );
	}

	/**
	 * Require the Action Scheduler plugin file if it exists.
	 * Makes sure that Action Scheduler gets loaded.
	 */
	public function require_plugin_file(): void {
		$plugin_file = $this->get_plugin_file();
		if ( ! empty( $plugin_file ) ) {
			require_once $plugin_file;
		}
	}

	/**
	 * Get the path to the Action Scheduler plugin file.
	 * It checks both the WP_PLUGIN_DIR and WPMU_PLUGIN_DIR directories.
	 *
	 * @return string|null The path to the Action Scheduler plugin file, or null if not found.
	 */
	private function get_plugin_file(): ?string {
		$path_options = array(
			FDMG_SELLIGENT_PLUGIN_PATH . 'vendor/woocommerce/action-scheduler/action-scheduler.php', // @phpstan-ignore-line constant.notFound
		);

		if ( defined( 'WP_PLUGIN_DIR' ) ) {
			$path_options[] = WP_PLUGIN_DIR . '/action-scheduler/action-scheduler.php';
		}
		if ( defined( 'WPMU_PLUGIN_DIR' ) ) {
			$path_options[] = WPMU_PLUGIN_DIR . '/action-scheduler/action-scheduler.php';
		}

		foreach ( $path_options as $path ) {
			if ( file_exists( $path ) ) {
				return $path;
			}
		}

		return null;
	}
}
