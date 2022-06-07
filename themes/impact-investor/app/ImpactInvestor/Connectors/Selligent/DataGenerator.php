<?php

namespace ImpactInvestor\Connectors\Selligent;

use Clarkson_Core\WordPress_Object\Clarkson_Object;
use Clarkson_Core\WordPress_Object\ii_newsletter;

class DataGenerator {
	/**
	 * Returns an array with the required values to send to Selligent.
	 */
	public static function get_article_data( Clarkson_Object $post ): array {
		$data = array(
			'publication'  => Client::PUBLICATION,
			'articleId'    => $post->get_id(),
			'title'        => substr( $post->get_title(), 0, 255 ),
			'teaser'       => substr( $post->get_excerpt(), 0, 2000 ),
			'news_section' => substr( '', 0, 50 ),
			'img'          => substr( get_the_post_thumbnail_url( $post->get_id() ) ?: '', 0, 255 ),
			'cta'          => $post->get_permalink(),
		);
		return $data;
	}

	/**
	 * Returns an array with the required values to send to Selligent. Keep in
	 * mind that the post ids given should already have been registered.
	 */
	public static function get_newsletter_data( ii_newsletter $newsletter, bool $test_mode ): array {
		$posts = array();
		foreach ( $newsletter->get_article_ids() as $key => $post_id ) {
			$article_key           = sprintf( 'article_%d', (int) $key + 1 );
			$posts[ $article_key ] = $post_id;
		}

		return array_merge(
			array(
				'publication'  => Client::PUBLICATION,
				'type'         => Client::get_type(),
				'subject'      => substr( $newsletter->get_subject(), 0, 255 ),
				'preheader'    => substr( $newsletter->get_pre_header(), 0, 255 ),
				'introduction' => substr( $newsletter->get_introduction(), 0, 2000 ),
				'test'         => $test_mode ? '1' : '0',
			), $posts
		);
	}
}
