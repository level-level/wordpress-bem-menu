<?php
namespace FDMGLabels;

use Clarkson\Theme\ACFPages\SiteSettings;

class Robots {
	public function register_hooks(): void {
		add_filter( 'robots_txt', array( $this, 'custom_robots_txt' ), 10, 1 ); // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.robots_txt
	}

	public function custom_robots_txt( string $output ): string {
		$theme     = get_option( sprintf( 'site_%s_%s', SiteSettings::OPTION_GROUP, SiteSettings::OPTION_ACTIVE_THEME ) );
		$site_name = 'Impact Investor';

		if ( $theme === 'energeia' ) {
			$site_name = 'Energeia';
		} elseif ( $theme === 'pensioen-pro' ) {
			$site_name = 'Pensioen Pro';
		}

		$custom_rules  = '# all use of ' . $site_name . " content is subject to the Terms & Conditions and Copyright Policy set out on fdmg.nl/copyright\n";
		$custom_rules .= '# scraping, harvesting or any (other) type of data- or text-mining of ' . $site_name . " content is not allowed\n";

		// Append the custom rules to the existing robots.txt content
		$output .= $custom_rules;

		return $output;
	}
}
