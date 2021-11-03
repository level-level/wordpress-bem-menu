<?php
namespace ImpactInvestor\KokoAnalytics;

use Clarkson_Core\Objects;

class KokoAnalytics {

	/**
	 * Returns 5 (five) most read posts from the last 7 (seven) days via Koko Analytics
	 */
	public static function get_most_viewed_articles( int $amount = 5 ): array {
		if ( ! is_plugin_active( 'koko-analytics/koko-analytics.php' ) ) {
			return array();
		}

		$args = array(
			'number'    => $amount,
			'post_type' => 'post',
			'days'      => 7,
		);

		if ( ! function_exists( '\KokoAnalytics\get_most_viewed_posts' ) ) {
			return array();
		}

		$posts   = \KokoAnalytics\get_most_viewed_posts( $args );
		$objects = Objects::get_instance()->get_objects( $posts );
		return $objects;
	}
}
