<?php
namespace ImpactInvestor\KokoAnalytics;

use Clarkson_Core\Objects;

class KokoAnalytics {

	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	/**
	* Make most read articles available in the theme
	*/
	public function add_context_args( array $context ): array {
		$context['most_read_articles'] = $this->get_most_viewed_articles();
		return $context;
	}

	/**
	 * Returns 5 (five) most read posts from the last 7 (seven) days via Koko Analytics
	 */
	public function get_most_viewed_articles(): array {
		if ( ! is_plugin_active( 'koko-analytics/koko-analytics.php' ) ) {
			return array();
		}

		$args = array(
			'number'    => 5,
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
