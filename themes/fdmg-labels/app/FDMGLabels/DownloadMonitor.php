<?php
namespace FDMGLabels;

class DownloadMonitor {

	public function register_hooks(): void {
		add_filter( 'pre_option_dlm_allowed_paths', array( $this, 'set_allowed_paths_from_wp_config' ) );
		add_filter( 'pre_option_download-monitor-rate-time', array( $this, 'set_rate_time' ) );
		add_action( 'init', array( $this, 'disable_session_on_no_access_page' ) );
	}

	/**
	 * Make the allowed paths setting configurable from wp-config.php.
	 *
	 * Please define them like the following example:
	 *
	 * define( 'LL_DLM_ALLOWED_PATHS', array(
	 *     '/srv/http/shared/',
	 *     '/srv/http/shared/uploads/',
	 *     '/srv/http/shared/uploads/dlm_uploads',
	 * ) );
	 *
	 * @param mixed $pre_option
	 * @return mixed
	 */
	public function set_allowed_paths_from_wp_config( $pre_option ) {
		if ( ! defined( 'LL_DLM_ALLOWED_PATHS' ) ) {
			return $pre_option;
		}

		$option_value = array();
		foreach ( constant( 'LL_DLM_ALLOWED_PATHS' ) as $index => $path ) {
			$option_value[] = array(
				'id'       => $index + 1,
				'path_val' => $path,
				'enabled'  => true,
			);
		}
		return $option_value;
	}

	/**
	 * Set the rate time to a day in the future to prevent DLM from constantly
	 * asking for a review in the wp-admin.
	 */
	public function set_rate_time( mixed $pre_option ): int {
		return time() + DAY_IN_SECONDS;
	}

	public function disable_session_on_no_access_page(): void {
		if ( function_exists( 'download_monitor' ) ) {
			remove_action( 'wp', array( download_monitor(), 'set_no_access_session' ) );
		}
	}
}
