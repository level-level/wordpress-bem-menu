<?php

namespace ImpactInvestor\Connectors\Selligent;

use Clarkson_Core\WordPress_Object\post;

class DataGenerator {
	/**
	 * Returns an array with the required values to send to Selligent.
	 */
	public static function get_article_data( post $post ): array {
		return array(
			'publication'  => Client::PUBLICATION,
			'articleId'    => $post->get_id(),
			'title'        => $post->get_title(),
			'teaser'       => $post->get_excerpt(),
			'news_section' => '',
			'img'          => get_the_post_thumbnail_url( $post->get_id() ),
			'cta'          => $post->get_permalink(),
		);
	}

	/**
	 * Returns an array with the required values to send to Selligent. Keep in
	 * mind that the post ids given should already have been registered.
	 *
	 * @param int[] $post_ids
	 */
	public static function get_newsletter_data( array $post_ids ): array {
		$posts = array();

		foreach ( $post_ids as $key => $post_id ) {
			$article_key           = sprintf( 'article_%d', $key + 1 );
			$posts[ $article_key ] = $post_id;
		}

		return array_merge(
			array(
				'publication'  => Client::PUBLICATION,
				'type'         => Client::get_type(),
				'subject'      => 'Newsletter test',
				'preheader'    => 'Pre header',
				'introduction' => 'Introduction',
			), $posts
		);
	}
}
