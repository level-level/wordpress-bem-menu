<?php

namespace FDMGLabels\CLI;

use WP_CLI;

class AcfJsonToAcfExtended {

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels acf-json-to-acf-extended run
	 */
	public function run( array $args, array $assoc_args ): void {
		$this->process_blocks();
		$this->process_widgets();
		$this->process_post_meta();
		$this->process_options();
		WP_CLI::success( 'All items have been processed' );
	}

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys for ACF blocks.
	 */
	protected function process_blocks(): void {
		/** @var \wpdb $wpdb */
		global $wpdb;

		WP_CLI::log( 'Processing blocks' );
		$blocks_map = $this->get_blocks_map();

		$command_options = array(
			'return'     => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json',              // Parse captured STDOUT to JSON array.
			'launch'     => false,               // Reuse the current process.
			'exit_error' => true,                // Halt script execution on error.
		);

		foreach ( $blocks_map as $block => $block_map ) {
			WP_CLI::log( sprintf( 'Processing block %s', $block ) );
			foreach ( $block_map as $old_acf_key => $new_acf_key ) {
				WP_CLI::log( sprintf( 'Replacing %s with %s', $old_acf_key, $new_acf_key ) );
				WP_CLI::runcommand(
					sprintf(
						'search-replace "%s" "%s" %s --report-changed-only',
						$old_acf_key,
						$new_acf_key,
						$wpdb->posts,
					), $command_options
				);
			}
		}
	}

	/**
	 * Get key-value array of ACF blocks and a field map for each.
	 * @return array<string,array<string,string>>
	 */
	protected function get_blocks_map(): array {
		return array(
			'cta'                 => array(
				'group_62b97a9b72ac4' => 'group_96e55b0b',
				'field_62b97ab12ceb3' => 'field_d13f5133',
				'field_62b97ad22ceb4' => 'field_aaba4b6e',
				'field_62b97af42ceb5' => 'field_cd878253',
				'field_62b97b192ceb6' => 'field_5296ae18',
				'field_62b97b472ceb7' => 'field_8d77fc04',
				'field_62b97b6b2ceb8' => 'field_1628aecb',
			),
			'articles-in-columns' => array(
				'group_607fe6ec214d3' => 'group_4289d8da',
				'field_607feb7bba1f8' => 'field_c97f5cae',
				'field_61810e09b64be' => 'field_239ae05a',
				'field_607fec20ed911' => 'field_5a948ea2',
				'field_607fed05ed912' => 'field_4b86982e',
				'field_607fef3dcd8ae' => 'field_0f185aa2',
				'field_607feff1cd8af' => 'field_d23b7dcb',
			),
			'featured-article'    => array(
				'group_616d8a919edfd' => 'group_81ea021c',
				'field_616d8a91a3ab9' => 'field_c1bdad2c',
				'field_616d8a91a3aea' => 'field_78b140fc',
				'field_616d8a91a3aef' => 'field_1d203b38',
				'field_616d8a91a3af0' => 'field_1d203b38',
			),
			'related-articles'    => array(
				'group_618a27e85624f' => 'group_53bccfec',
				'field_618a27e8856a3' => 'field_e3d1345c',
				'field_618a2bd8cfc99' => 'field_ce559145',
				'field_618a27e8856d2' => 'field_aeff6b6c',
				'field_618a27e8856d7' => 'field_e71de6c8',
				'field_618a27e8856f6' => 'field_57bae3f0',
				'field_618a27e88573a' => 'field_90bdab05',
			),
			'team'                => array(
				'group_618bd9c86c510' => 'group_d3ccb7d6',
				'field_618bd9c8a0cac' => 'field_0da8d7eb',
				'field_618bd9e77079a' => 'field_3286ae42',
			),
			'partners'            => array(
				'group_618be121e8146' => 'group_520c0ba8',
				'field_618be1222774f' => 'field_c12b2879',
				'field_618be1222776d' => 'field_37e4fbee',
			),
			'highlighted-content' => array(
				'group_61793e86133eb' => 'group_8e1fb49e',
				'field_61793e994e07b' => 'field_91e9c2a3',
				'field_61793ed44e07c' => 'field_d75e3e94',
			),
			'advertisement'       => array(
				'group_618140098fa4b' => 'group_8fdb3fac',
				'field_61a772bbd72bc' => 'field_d4ba69db',
			),
		);
	}

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys for ACF widgets.
	 */
	protected function process_widgets(): void {
		/** @var \wpdb $wpdb */
		global $wpdb;

		WP_CLI::log( 'Processing widgets' );
		$widgets_map = $this->get_widgets_map();

		$command_options = array(
			'return'     => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json',              // Parse captured STDOUT to JSON array.
			'launch'     => false,               // Reuse the current process.
			'exit_error' => true,                // Halt script execution on error.
		);

		foreach ( $widgets_map as $widget => $widget_map ) {
			WP_CLI::log( sprintf( 'Processing widget %s', $widget ) );
			foreach ( $widget_map as $old_acf_key => $new_acf_key ) {
				WP_CLI::log( sprintf( 'Replacing %s with %s', $old_acf_key, $new_acf_key ) );
				WP_CLI::runcommand(
					sprintf(
						'search-replace "%s" "%s" %s --report-changed-only',
						$old_acf_key,
						$new_acf_key,
						$wpdb->options,
					), $command_options
				);
			}
		}
	}

	/**
	 * Get key-value array of ACF widgets and a field map for each.
	 * @return array<string,array<string,string>>
	 */
	protected function get_widgets_map(): array {
		return array(
			// Advertisement is the only block that's also made available as widget
			'advertisement'   => array(
				'group_618140098fa4b' => 'group_8fdb3fac',
				'field_61a772bbd72bc' => 'field_d4ba69db',
			),
			'vacancies'       => array(
				'group_618263e04e3ed' => 'group_27e7ee3f',
				'field_618263e092dcd' => 'field_ef0ee867',
				'field_618263e092df7' => 'field_45a23355',
				'field_618263e092e2d' => 'field_c958cf25',
				'field_618263e092eab' => 'field_8bf45d5c',
			),
			'latest-articles' => array(
				'group_6183a6e2a8e00' => 'group_10bc9ff5',
				'field_6183a70de25bd' => 'field_23e28c42',
			),
		);
	}

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys for ACF postmeta.
	 */
	protected function process_post_meta(): void {
		/** @var \wpdb $wpdb */
		global $wpdb;

		WP_CLI::log( 'Processing post meta' );
		$post_meta_map = $this->get_post_meta_map();

		$command_options = array(
			'return'     => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json',              // Parse captured STDOUT to JSON array.
			'launch'     => false,               // Reuse the current process.
			'exit_error' => true,                // Halt script execution on error.
		);

		foreach ( $post_meta_map as $post_meta => $post_meta_map ) {
			WP_CLI::log( sprintf( 'Processing post meta %s', $post_meta ) );
			foreach ( $post_meta_map as $old_acf_key => $new_acf_key ) {
				WP_CLI::log( sprintf( 'Replacing %s with %s', $old_acf_key, $new_acf_key ) );
				WP_CLI::runcommand(
					sprintf(
						'search-replace "%s" "%s" %s --report-changed-only',
						$old_acf_key,
						$new_acf_key,
						$wpdb->postmeta,
					), $command_options
				);
			}
		}
	}

	/**
	 * Get key-value array of post types and an ACF field map for each.
	 * @return array<string,array<string,string>>
	 */
	protected function get_post_meta_map(): array {
		return array(
			// Advertisement is the only block that's also made available as widget
			'partner_data' => array(
				'group_618baba9b6a83' => 'group_2a93cfc2',
				'field_618baba9ea2f0' => 'field_1aae7aeb',
			),
			'post_authors' => array(
				'group_617a402184288' => 'group_df1b5078',
				'field_617a40444752a' => 'field_148aff5f',
			),
			'team_author_data'    => array(
				'group_617a60ad5295b' => 'group_b85016b3',
				'field_617a6114e4517' => 'field_a9f68cbc',
				'field_617a6143e4518' => 'field_6d100c7e',
				'field_618923758495d' => 'field_4f031873',
			),
		);
	}

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys for ACF options.
	 */
	protected function process_options(): void {
		/** @var \wpdb $wpdb */
		global $wpdb;

		WP_CLI::log( 'Processing options' );
		$options_map = $this->get_options_map();

		$command_options = array(
			'return'     => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json',              // Parse captured STDOUT to JSON array.
			'launch'     => false,               // Reuse the current process.
			'exit_error' => true,                // Halt script execution on error.
		);

		foreach ( $options_map as $option => $option_map ) {
			WP_CLI::log( sprintf( 'Processing option %s', $option ) );
			foreach ( $option_map as $old_acf_key => $new_acf_key ) {
				WP_CLI::log( sprintf( 'Replacing %s with %s', $old_acf_key, $new_acf_key ) );
				WP_CLI::runcommand(
					sprintf(
						'search-replace "%s" "%s" %s --report-changed-only',
						$old_acf_key,
						$new_acf_key,
						$wpdb->options,
					), $command_options
				);
			}
		}
	}

	/**
	 * Get key-value array of ACF options and a field map for each.
	 * @return array<string,array<string,string>>
	 */
	protected function get_options_map(): array {
		return array(
			'theme_options' => array(
				'group_607e75073ae16' => 'group_542ec9a3',
				'field_607e751d8bd50' => 'field_87022bd4',
				'field_618232653bfac' => 'field_88692ed2',
				'field_6182327a3bfad' => 'field_1ffc2416',
				'field_6182328e3bfae' => 'field_9049852b',
				'field_618232ac3bfaf' => 'field_9de7fd8d',
				'field_618232d03bfb0' => 'field_e644e86f',
				'field_618233073bfb1' => 'field_e3a68c50',
				'field_619e47f21b38f' => 'field_4fe8f2b7',
				'field_619e47fc1b390' => 'field_70de407b',
				'field_61a7754ea9949' => 'field_373d51cb',
				'field_61a7755da994a' => 'field_631e8895',
			),
		);
	}

	/**
	 * The key-value array where the key is the old field key, and the value the new field key.
	 *
	 * @return array<string,string>
	 */
	protected function get_full_map(): array {
		$map = array();

		$blocks_map = $this->get_blocks_map();
		foreach ( $blocks_map as $block_map ) {
			$map = array_merge( $map, $block_map );
		}

		$widgets_map = $this->get_widgets_map();
		foreach ( $widgets_map as $widget_map ) {
			$map = array_merge( $map, $widget_map );
		}

		$posts_meta_map = $this->get_post_meta_map();
		foreach ( $posts_meta_map as $post_meta_map ) {
			$map = array_merge( $map, $post_meta_map );
		}

		$options_map = $this->get_options_map();
		foreach ( $options_map as $option_map ) {
			$map = array_merge( $map, $option_map );
		}

		return $map;
	}

}
