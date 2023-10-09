<?php
namespace FDMGLabels;

use Clarkson\Theme\ACFPages\ThemeOptions;

class Robots {
	public function register_hooks(): void {
		add_filter( 'robots_txt', array( $this, 'custom_robots_txt' ), 10, 1 );
	}

	public function custom_robots_txt( string $output ): string {
		$theme    = get_option( sprintf( 'options_%s_%s', ThemeOptions::OPTION_GROUP, ThemeOptions::OPTION_ACTIVE_THEME ) );
		$siteName = 'Impact Investor';

		if ( $theme === 'energeia' ) {
			$siteName = 'Energeia';
		} elseif ( $theme === 'pensioen-pro' ) {
			$siteName = 'Pensioen Pro';
		}

		$custom_rules  = '# all use of ' . $siteName . " content is subject to the Terms & Conditions and Copyright Policy set out on fdmg.nl/copyright\n";
		$custom_rules .= '# scraping, harvesting or any (other) type of data- or text-mining of ' . $siteName . " content is not allowed\n";

		// Append the custom rules to the existing robots.txt content
		$output .= $custom_rules;

		return $output;
	}
}
