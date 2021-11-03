<?php

namespace ImpactInvestor\Articles;

class ArticleBlockRegister {
	/** @var ArticleBlockRegister|null */
	private static $instance;

	/** @var array */
	private $shown_ids = array();

	public static function get_instance(): ArticleBlockRegister {
		if ( ! is_object( self::$instance ) ) {
			self::$instance = new ArticleBlockRegister();
		}

		return self::$instance;
	}

	/**
	 * @param array $ids
	 */
	public function add_ids( array $ids ): void {
		$this->shown_ids = array_merge( $this->shown_ids, $ids );
	}

	public function get_ids(): array {
		return $this->shown_ids;
	}

	/**
	 * Adds the post__not_in argument to the query args provided, if the array
	 * of already shown posts has post ids in it.
	 *
	 * @param array $args
	 * @return array
	 */
	public function maybe_add_id_exclude_to_query_args( array $args ): array {
		$shown_ids = array_unique( $this->get_ids() );
		if ( ! empty( $shown_ids ) ) {
			$args['post__not_in'] = array_merge( $args['post__not_in'], $shown_ids );
		}

		return $args;
	}
}
