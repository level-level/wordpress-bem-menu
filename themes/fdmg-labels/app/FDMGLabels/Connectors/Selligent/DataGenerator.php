<?php

namespace FDMGLabels\Connectors\Selligent;

use Clarkson\Theme\PostTypes\Newsletter;
use Clarkson\Theme\Taxonomies\Section;
use Clarkson\Utilities\Image;
use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\ii_newsletter;
use Clarkson_Core\WordPress_Object\post;
use DateTime;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use FDMGLabels\FeatureManager;

class DataGenerator {
	/**
	 * Returns an array with the required values to send to Selligent.
	 */
	public function get_article_data( base_object $post, ii_newsletter $newsletter ): array {
		$feature_manager = new FeatureManager();
		$data            = array(
			'publication'      => $newsletter->get_publication(),
			'articleId'        => $post->get_id(),
			'title'            => substr( $post->get_title(), 0, 255 ),
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

			if ( $feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
				$primary_section = $post->get_primary_term( Section::TAXONOMY_NAME );
				if ( $primary_section ) {
					$news_section[] = $primary_section->get_name();
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
	 * @param Newsletter::META_SEND_TEST_OPTION_* $test_mode
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

	protected function format_publish_date( base_object $object ): string {
		$current_timezone    = wp_timezone();
		$timezone_gmt_offset = $current_timezone->getOffset( new DateTime( 'now' ) );

		// Default to full date
		$format = 'd F Y';

		// If post is published in same year, only show date and month
		$current_year = date_i18n( 'Y', strtotime( 'now' ) + $timezone_gmt_offset );
		$post_year    = date_i18n( 'Y', strtotime( $object->get_object()->post_date_gmt ) + $timezone_gmt_offset );
		if ( $current_year === $post_year ) {
			$format = 'd F';
		}

		// If post is published on same day, only show time
		$current_day = date_i18n( 'Ymd', strtotime( 'now' ) + $timezone_gmt_offset );
		$post_day    = date_i18n( 'Ymd', strtotime( $object->get_object()->post_date_gmt ) + $timezone_gmt_offset );
		if ( $current_day === $post_day ) {
			$format = 'H:i';
		}

		return date_i18n( $format, strtotime( $object->get_object()->post_date_gmt ) + $timezone_gmt_offset );
	}
}
