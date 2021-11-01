<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson_Core\WordPress_Object\post;

class ii_team extends base_object {
	public static $type = 'ii_team';

	public function get_posts_by_author( int $amount = -1 ): array {
		$args = array(
			'posts_per_page' => $amount,
			'meta_query'     => array(
				array(
					'key'     => 'authors',
					'value'   => $this->get_id(),
					'compare' => 'LIKE',
				),
			),
		);

		return post::get_many( $args );
	}

	public function get_email_address(): string {
		return get_field( 'email_address', $this->get_id() ) ?: '';
	}

	public function get_linkedin(): string {
		return get_field( 'linkedin', $this->get_id() ) ?: '';
	}
}
