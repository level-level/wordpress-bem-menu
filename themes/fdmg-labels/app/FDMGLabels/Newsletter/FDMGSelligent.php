<?php
namespace FDMGLabels\Newsletter;

use Clarkson\Theme\Taxonomies\Category;
use Clarkson\Theme\Taxonomies\Genre;
use Clarkson\Theme\Taxonomies\Section;
use Clarkson_Core\Objects;
use Clarkson_Core\WordPress_Object\base_object;
use Clarkson_Core\WordPress_Object\post;
use FDMGLabels\FeatureManager;
use LevelLevel\FDMGSelligent\Objects\Newsletter as FDMGSelligentNewsletterObject;
use WP_Post;

class FDMGSelligent {

	public function register_hooks(): void {
		add_filter( 'fdmg_selligent_newsletter_taxomies', array( $this, 'set_taxonomies' ) );
		add_filter( 'fdmg_selligent_generated_article_data', array( $this, 'set_article_data' ), 10, 3 );
	}

	/**
	 * Set the taxonomies to be able to filter articles for in the Selligent newsletter.
	 *
	 * @param string[] $taxonomies
	 * @return string[]
	 */
	public function set_taxonomies( array $taxonomies ): array {
		$feature_manager = new FeatureManager();
		$taxonomies      = array();

		if ( $feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
			$taxonomies[] = Genre::TAXONOMY_NAME;
		}

		if ( $feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_SECTION_TAXONOMY ) ) {
			$taxonomies[] = Section::TAXONOMY_NAME;
		}

		if ( ! $feature_manager->is_enabled( FeatureManager::FEATURE_DISABLE_CATEGORY_TAXONOMY ) ) {
			$taxonomies[] = Category::TAXONOMY_NAME;
		}
		return $taxonomies;
	}

	/**
	 * Set extra data for articles in a Selligent newsletter.
	 *
	 * @param array<string,mixed> $data
	 * @param WP_Post $post
	 * @param FDMGSelligentNewsletterObject $newsletter
	 * @return array<string,mixed>
	 */
	public function set_article_data( array $data, WP_Post $post, FDMGSelligentNewsletterObject $newsletter ): array {
		$feature_manager = new FeatureManager();

		/** @var base_object */
		$clarkson_object = Objects::get_instance()->get_object( $post );

		// Set the title as the alternative title, or the post title
		$title = $clarkson_object->get_title();
		if ( $clarkson_object instanceof post ) {
			$title = $clarkson_object->get_alternative_title() ?: $title;
		}
		$data['title'] = substr( $title, 0, 255 );

		// Set reading time
		$data['reading_time'] = $clarkson_object->get_reading_time_in_minutes();

		// Set news section to genres
		if ( $clarkson_object instanceof post ) {
			$news_section = array();

			// If genres are enabled and post has primary genre, add it to the article data in the news_section field.
			// Please note that we should not use the Section taxonomy to fill the news_section field.
			if ( $feature_manager->is_enabled( FeatureManager::FEATURE_ENABLE_GENRE_TAXONOMY ) ) {
				$primary_genre = $clarkson_object->get_primary_term( Genre::TAXONOMY_NAME );
				if ( $primary_genre ) {
					$news_section[] = $primary_genre->get_name();
				}
			}

			$data['news_section'] = substr( implode( ', ', $news_section ), 0, 50 );
		}

		return $data;
	}
}
