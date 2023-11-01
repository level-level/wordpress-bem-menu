<?php
namespace FDMGLabels\Newsletter;

use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use DateTimeImmutable;
use DateTimeInterface;

class MergeTagsReplacer {

	public function replace( ii_newsletter $newsletter, string $text ): string {
		$text = $this->replace_first_article_title( $newsletter, $text );
		$text = $this->replace_date_now( $text );
		$text = $this->replace_date_now_format( $text );
		return $text;
	}

	/**
	 * Get the first article, and replace the merge tag with the title of the article.
	 */
	protected function replace_first_article_title( ii_newsletter $newsletter, string $text ): string {
		$first_article_title = '';
		$article_groups      = $newsletter->get_article_groups();
		foreach ( $article_groups as $article_groups ) {
			$article_ids = $article_groups->get_article_ids();
			foreach ( $article_ids as $article_id ) {
				$article = post::get( $article_id );
				if ( ! $article instanceof post ) {
					continue;
				}

				$first_article_title = $article->get_title();
				break;
			}

			if ( ! empty( $first_article_title ) ) {
				break;
			}
		}

		$text = str_replace( '{first-article-title}', $first_article_title, $text );
		return $text;
	}

	protected function replace_date_now( string $text ): string {
		$formatted_date = $this->format_date( get_option( 'date_format' ), new DateTimeImmutable( 'now', wp_timezone() ) );
		$text           = str_replace( '{date-now}', $formatted_date, $text );
		return $text;
	}

	protected function replace_date_now_format( string $text ): string {
		$matches        = array();
		$number_matches = preg_match_all( '/{date-now-format-([^{}]+)}/', $text, $matches, PREG_SET_ORDER );
		if ( ! $number_matches ) {
			return $text;
		}

		foreach ( $matches as $match ) {
			$date = $this->format_date( $match[1], new DateTimeImmutable( 'now', wp_timezone() ) );
			$text = str_replace( $match[0], $date, $text );
		}
		return $text;
	}

	protected function format_date( string $format, DateTimeInterface $post_date ): string {
		return date_i18n( $format, $post_date->getTimestamp() );
	}
}
