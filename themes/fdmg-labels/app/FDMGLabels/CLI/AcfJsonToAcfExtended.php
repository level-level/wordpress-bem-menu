<?php

namespace FDMGLabels\CLI;

use WP_CLI;

use function WP_CLI\Utils\get_flag_value;

class AcfJsonToAcfExtended {

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## Options
	 *
	 * [--start-index=<int>]
	 * : At what line to start processing
	 * ---
	 * default: 0
	 * ---
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels acf-json-to-acf-extended run --start-index=0
	 */
	public function run( array $args, array $assoc_args ): void {
		$start_index = (int) get_flag_value( $assoc_args, 'start-index', 0 );
		$map = $this->get_map();

		$loop = 0;

		$command_options = array(
			'return'       => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'        => 'json',              // Parse captured STDOUT to JSON array.
			'launch'       => false,               // Reuse the current process.
			'exit_error'   => true,                // Halt script execution on error.
		);
		foreach ( $map as $old_key => $new_key ) {
			if ( $loop < $start_index ) {
				continue;
			}

			WP_CLI::log( sprintf( 'Processing index %d, replacing %s with %s', $loop, $old_key, $new_key ) );
			WP_CLI::runcommand( sprintf(
				'search-replace "%s" "%s" --all-tables --report-changed-only',
				$old_key,
				$new_key
			), $command_options );
		}
		WP_CLI::success( 'All items have been processed' );
	}

	protected function get_map(): array {
		return array(
			// Block: CTA
			'group_62b97a9b72ac4' => 'group_96e55b0b',
			'field_62b97ab12ceb3' => 'field_d13f5133',
			'field_62b97ad22ceb4' => 'field_aaba4b6e',
			'field_62b97af42ceb5' => 'field_cd878253',
			'field_62b97b192ceb6' => 'field_5296ae18',
			'field_62b97b472ceb7' => 'field_8d77fc04',
			'field_62b97b6b2ceb8' => 'field_1628aecb',
		);
	}

}
