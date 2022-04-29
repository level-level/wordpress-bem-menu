<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Newsletter;

class ii_newsletter extends base_object {
	public static $type = 'ii_newsletter';

	public function get_subject(): string {
		return $this->get_meta( Newsletter::META_SUBJECT, true );
	}

	public function get_pre_header(): string {
		return $this->get_meta( Newsletter::META_PRE_HEADER, true );
	}

	public function get_introduction(): string {
		return (string) get_field( Newsletter::META_INTRODUCTION, $this->get_id() );
	}

	/**
	 * Get the articles that should be displayed in the newsletter
	 *
	 * @return array<int,array<string,mixed>>
	 */
	public function get_articles(): array {
		$articles = get_field( Newsletter::META_ARTICLES, $this->get_id() );
		if ( ! is_array( $articles ) ) {
			return array();
		}
		return $articles;
	}

	/**
	 * Get the articles that should be displayed in the newsletter
	 *
	 * @return int[]
	 */
	public function get_article_ids(): array {
		$articles = $this->get_articles();

		$article_ids = array_map(
			function( array $articles_row ): int {
				return (int) ( $articles_row[ Newsletter::META_ARTICLES_ARTICLE ] ?? 0 );
			}, $articles
		);

		return array_filter( $article_ids );
	}

	/**
	 * Get the tips that should be displayed in the newsletter
	 *
	 * @return post[]
	 */
	public function get_article_objects(): array {
		$ids  = $this->get_article_ids();
		$articles = array();
		foreach ( $ids as $id ) {
			$article = post::get( $id );
			if ( $article instanceof post ) {
				$articles[] = $article;
			}
		}
		return $articles;
	}
}
