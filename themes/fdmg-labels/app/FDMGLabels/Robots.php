<?php
namespace FDMGLabels;

use Clarkson\Theme\ACFPages\SEOOptions;

class Robots {
	public function register_hooks(): void {
		// phpcs:disable WordPressVIPMinimum.Hooks.RestrictedHooks.robots_txt
		add_filter( 'robots_txt', array( $this, 'overwrite_robots_txt' ), PHP_INT_MAX, 1 );
		// phpcs:enable WordPressVIPMinimum.Hooks.RestrictedHooks.robots_txt
	}

	/**
	 * Overwrite the robots.txt file with custom content.
	 */
	public function overwrite_robots_txt( string $original_output ): string {
		$new_output = array(
			$this->get_copyright_notice(),
			$this->get_fdmg_configurable_block(),
			$this->get_stripped_yoast_block( $original_output ),
		);

		return implode( PHP_EOL . PHP_EOL, $new_output );
	}

	/**
	 * If a copyright notice is set, return it as a comment for the robots.txt file.
	 */
	public function get_copyright_notice(): string {
		$copyright_notice = get_option( sprintf( 'options_%s_%s', SEOOptions::OPTION_GROUP_ROBOTS, SEOOptions::OPTION_ROBOTS_COPYRIGHT_NOTICE ) );
		if ( empty( $copyright_notice ) ) {
			return '';
		}

		$lines                  = array(
			'# COPYRIGHT NOTICE',
		);
		$copyright_notice_lines = explode( PHP_EOL, $copyright_notice );
		foreach ( $copyright_notice_lines as $line ) {
			$lines[] = '# ' . $line;
		}

		return implode( PHP_EOL, $lines );
	}

	/**
	 * Comments out all robots.txt lines from yoast block except for sitemaps.
	 * This allows yoast to still add sitemaps to the robots.txt file, but allows
	 * us to customize the rest.
	 */
	protected function get_stripped_yoast_block( string $original_output ): string {
		$original_output_lines = explode( PHP_EOL, $original_output );

		$yoast_lines    = array();
		$in_yoast_block = false;
		foreach ( $original_output_lines as $line ) {
			if ( str_contains( $line, '# START YOAST BLOCK' ) ) {
				$in_yoast_block = true;
				$yoast_lines[]  = $line;
				continue;
			}

			if ( str_contains( $line, '# END YOAST BLOCK' ) !== false ) {
				$in_yoast_block = false;
				$yoast_lines[]  = $line;
				continue;
			}

			// If not in yoast block, no need to do anything
			if ( ! $in_yoast_block ) {
				continue;
			}

			// Skip comments or empty lines, no need to do anything
			if ( empty( $line ) || str_starts_with( $line, '#' ) ) {
				$yoast_lines[] = $line;
				continue;
			}

			// If the line starts with 'Sitemap:', no need to do anything
			if ( str_starts_with( $line, 'Sitemap:' ) ) {
				$yoast_lines[] = $line;
				continue;
			}

			// Comment out the line, and add a note it's disabled by this theme
			$yoast_lines[] = '# ' . $line . ' # Disabled by FDMG Labels';
		}
		return implode( PHP_EOL, $yoast_lines );
	}

	/**
	 * If a custom content for the robots.txt file is set, return it.
	 */
	protected function get_fdmg_configurable_block(): string {
		$custom_content = get_option( sprintf( 'options_%s_%s', SEOOptions::OPTION_GROUP_ROBOTS, SEOOptions::OPTION_ROBOTS_CUSTOM_CONTENT ) );
		if ( empty( $custom_content ) ) {
			return '';
		}

		$output = array(
			'# START FDMG BLOCK',
			'-------------------------',
			$custom_content,
			'-------------------------',
			'# END FDMG BLOCK',
		);
		return implode( PHP_EOL, $output );
	}
}
