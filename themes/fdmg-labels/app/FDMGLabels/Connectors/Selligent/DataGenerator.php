<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Clarkson\Utilities\Image;
use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use DateTime;
use DateTimeImmutable;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use FDMGLabels\FeatureManager;

class DataGenerator {
	/**
	 * Returns an array with the required values to send to Selligent.
	 */
	public function get_article_data( base_object $post, ii_newsletter $newsletter ): array {
		$feature_manager = new FeatureManager();
		$title           = $post->get_title();
		if ( $post instanceof post ) {
			$title = $post->get_alternative_title() ?: $title;
		}
		$data = array(
			'publication'      => $newsletter->get_publication(),
			'articleId'        => $post->get_id(),
			'title'            => substr( $title, 0, 255 ),
			'teaser'           => substr( $post->get_excerpt(), 0, 2000 ),
			'news_section'     => substr( '', 0, 50 ),
			'img'              => '',
			'img_square'       => '',
			'cta'              => $post->get_permalink(),
			'reading_time'     => $post->get_reading_time_in_minutes(),
			'publication_time' => $this->format_publish_date( $post ),
		);

		$thumbnail_id = get_post_thumbnail_id( $post->get_object() );
		if ( $thumbnail_id ) {
			// Large image
			$large_image_source = wp_get_attachment_image_src( $thumbnail_id, 'full' );
			$large_image_source = is_array( $large_image_source ) ? $large_image_source[0] : '';
			$data['img']        = substr( $large_image_source, 0, 255 );

			// Square image
			$square_thumbnail = ( new Image() )->get_largest_aspect_ratio_image_size( $thumbnail_id, 1, 1, true );
			if ( $square_thumbnail ) {
				$data['img_square'] = substr( $square_thumbnail['source'], 0, 255 );
			}
		}

		if ( $post instanceof post ) {
			$news_section = array();

			// If genres are enabled and post has primary genre, add it to the article data in the news_section field.
			// Please note that we should not use the Section taxonomy to fill the news_section field.
			if ( $feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
				$primary_genre = $post->get_primary_term( Genre::TAXONOMY_NAME );
				if ( $primary_genre ) {
					$news_section[] = $primary_genre->get_name();
				}
			}

			$data['news_section'] = substr( implode( ', ', $news_section ), 0, 50 );
		}

		return $data;
	}

	/**
	 * Returns an array with the required values to send to Selligent. Keep in
	 * mind that the post ids given should already have been registered.
	 *
	 * @param ii_newsletter $newsletter
	 * @param Newsletter::META_PREVIEW_RECIPIENTS_OPTION_* $test_mode
	 * @return array<string,string>
	 */
	public function get_newsletter_data( ii_newsletter $newsletter, string $test_mode ): array {
		$data = array(
			'publication'  => substr( $newsletter->get_publication(), 0, 50 ),
			'type'         => substr( $newsletter->get_type(), 0, 50 ),
			'subject'      => substr( $newsletter->get_subject(), 0, 255 ),
			'preheader'    => substr( $newsletter->get_pre_header(), 0, 255 ),
			'introduction' => substr( $newsletter->get_introduction(), 0, 2000 ),
			'test'         => $test_mode,
		);

		foreach ( $newsletter->get_article_groups() as $index => $article_group ) {
			$data[ 'articlegroup_' . ( $index + 1 ) ] = implode( ',', $article_group->get_article_ids() );
		}

		return $data;
	}

	protected function format_publish_date( base_object $base_object ): string {
		$now_date         = new DateTimeImmutable( 'now', wp_timezone() );
		$publication_date = new DateTimeImmutable( $base_object->get_object()->post_date, wp_timezone() );

		// Default to full date
		$format = 'd F Y';

		// If post is published in same year, only show date and month
		$current_year = date_i18n( 'Y', $now_date->getTimestamp() );
		$post_year    = date_i18n( 'Y', $publication_date->getTimestamp() );
		if ( $current_year === $post_year ) {
			$format = 'd F';
		}

		// If post is published on same day, only show time
		$current_day = date_i18n( 'Ymd', $now_date->getTimestamp() );
		$post_day    = date_i18n( 'Ymd', $publication_date->getTimestamp() );
		if ( $current_day === $post_day ) {
			$format = 'H:i';
		}

		return date_i18n( $format, $publication_date->getTimestamp() );
	}
}
