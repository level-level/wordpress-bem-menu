<?php
namespace ImpactInvestor\Articles;

use Clarkson_Core\WordPress_Object\post;

class Articles {

	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	/**
	* Make latest articles available in the theme
	*/
	public function add_context_args( array $context ): array {
		$context['latest_articles'] = $this->get_latest_articles();
		return $context;
	}

	/**
	 * Returns the 5 (five) latest posts
	 */
	public function get_latest_articles(): array {
		$args = array(
			'posts_per_page' => 5,
		);

		return post::get_many( $args );
	}
}
