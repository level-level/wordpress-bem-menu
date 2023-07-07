<?php

namespace FDMGLabels\CLI;

use WP_CLI;

use function WP_CLI\Utils\get_flag_value;

class AcfJsonToAcfExtended {

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys using DB search-replace actions.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * @subcommand search-replace
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels acf-json-to-acf-extended search-replace
	 */
	public function search_replace( array $args, array $assoc_args ): void {
		$map = $this->get_full_map();

		$loop = 0;

		$command_options = array(
			'return'     => true,                // Return 'STDOUT'; use 'all' for full object.
			'parse'      => 'json',              // Parse captured STDOUT to JSON array.
			'launch'     => false,               // Reuse the current process.
			'exit_error' => true,                // Halt script execution on error.
		);
		foreach ( $map as $old_key => $new_key ) {
			WP_CLI::log( sprintf( 'Processing index %d, replacing %s with %s', $loop, $old_key, $new_key ) );
			WP_CLI::runcommand(
				sprintf(
					'search-replace "%s" "%s" --all-tables --report-changed-only',
					$old_key,
					$new_key
				), $command_options
			);
			$loop++;
		}
		WP_CLI::success( 'All items have been processed' );
	}

	/**
	 * Map old ACF JSON field keys to new ACF JSON field keys updating objects separately.
	 *
	 * @param string[] $args
	 * @param array<string,string> $assoc_args
	 *
	 * ## EXAMPLES
	 *
	 * wp fdmg-labels acf-json-to-acf-extended objects
	 */
	public function objects( array $args, array $assoc_args ): void {
		$this->process_blocks();
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
		foreach ( $blocks_map as $block => $block_map ) {
			WP_CLI::log( sprintf( 'Processing block %s', $block ) );
			foreach ( $block_map as $old_acf_key => $new_acf_key ) {
				WP_CLI::log( sprintf( 'Replacing %s with %s', $old_acf_key, $new_acf_key ) );

				$posts = $wpdb->get_results(
					$wpdb->prepare( // @phpstan-ignore-line
						"SELECT ID, post_content FROM {$wpdb->posts} WHERE post_content LIKE %s",
						'%' . $wpdb->esc_like( $old_acf_key ) . '%',
					)
				);
				if ( ! is_array( $posts ) ) {
					continue;
				}

				foreach ( $posts as $post ) {
					WP_CLI::log( sprintf( 'Replacing %s with %s in post ID %d', $old_acf_key, $new_acf_key, $post->ID ) );
					$post_content = $post->post_content;
					$post_content = str_replace( $old_acf_key, $new_acf_key, $post_content );
					$wpdb->update(
						$wpdb->posts,
						array(
							'post_content' => $post_content,
						),
						array(
							'ID' => $post->ID,
						),
						array(
							'%s',
						),
						array(
							'%d',
						)
					);
				}
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

		return $map;
	}

}
