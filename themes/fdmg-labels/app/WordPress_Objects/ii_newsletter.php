<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Newsletter;
use Extended\ACF\Key;
use FDMGLabels\Connectors\Selligent\Settings\NewsletterTypeSettings;
use FDMGLabels\Connectors\Selligent\Settings\Settings;
use FDMGLabels\Newsletter\ArticleGroup;
use FDMGLabels\Newsletter\MergeTagsReplacer;

class ii_newsletter extends base_object {
	public static $type = Newsletter::POST_TYPE_NAME;

	public function get_publication(): string {
		return $this->get_meta( Newsletter::META_PUBLICATION, true );
	}

	public function get_type(): string {
		return $this->get_meta( Newsletter::META_TYPE, true );
	}

	public function get_subject(): string {
		$subject = (string) $this->get_meta( Newsletter::META_SUBJECT, true );
		return ( new MergeTagsReplacer() )->replace( $this, $subject );
	}

	public function get_pre_header(): string {
		$pre_header = (string) $this->get_meta( Newsletter::META_PRE_HEADER, true );
		return ( new MergeTagsReplacer() )->replace( $this, $pre_header );
	}

	public function get_introduction(): string {
		$introduction = (string) get_field( Newsletter::META_INTRODUCTION, $this->get_id() );
		return ( new MergeTagsReplacer() )->replace( $this, $introduction );
	}

	/**
	 * Get the article groups of the newsletter.
	 * Always returns the amount of article groups corresponding to the newsletter type
	 *
	 * @return array<ArticleGroup>
	 */
	public function get_article_groups(): array {
		// Detect the current newsletter type settings
		$newsletter_type_settings = Settings::get_instance()->get_newsletter()->get_types();
		$current_type             = array_filter(
			$newsletter_type_settings,
			function ( NewsletterTypeSettings $type_setting ) {
				return $type_setting->get_type() === $this->get_type();
			}
		);
		$current_type             = array_shift( $current_type );
		if ( ! $current_type ) {
			return array();
		}

		// Get newsletter article groups based on the current newsletter type settings
		$meta_article_groups                        = get_field( Newsletter::META_ARTICLE_GROUPS_PREFIX . Key::sanitize( $this->get_type() ), $this->get_id() );
		$article_groups                             = array();
		$settings_current_type_article_groups_count = count( $current_type->get_article_groups() );
		for ( $index = 0; $index < $settings_current_type_article_groups_count; $index++ ) {
			$acf_article_group = $meta_article_groups[ Newsletter::META_ARTICLE_GROUP_PREFIX . $index ] ?? array();
			$acf_article_group = is_array( $acf_article_group ) ? $acf_article_group : array();
			$article_groups[]  = new ArticleGroup( $acf_article_group );
		}
		return $article_groups;
	}
}
