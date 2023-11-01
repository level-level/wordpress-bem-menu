<?php
namespace FDMGLabels;

use Clarkson\Theme\ACFPages\Features;

class FeatureManager {

	// Post types
	public const FEATURE_ENABLE_PARTNER_POST_TYPE = 'enable_partner_post_type';
	public const FEATURE_ENABLE_EVENT_POST_TYPE   = 'enable_event_post_type';

	// Taxonomies
	public const FEATURE_ENABLE_GENRE_TAXONOMY     = 'enable_genre_taxonomy';
	public const FEATURE_ENABLE_SECTION_TAXONOMY   = 'enable_section_taxonomy';
	public const FEATURE_DISABLE_CATEGORY_TAXONOMY = 'disable_category_taxonomy';

	// Layout options
	public const FEATURE_ENABLE_COMPACT_HEADER         = 'enable_compact_header';
	public const FEATURE_DISABLE_ARTICLE_TEASER_FOOTER = 'disable_artice_teaser_footer';
	public const FEATURE_ENABLE_PAYWALL_LABEL          = 'enable_paywall_label';

	// Share options
	public const FEATURE_SHARE_VIA_FACEBOOK       = 'enable_share_via_facebook';
	public const FEATURE_SHARE_VIA_TWITTER        = 'enable_share_via_twitter';
	public const FEATURE_SHARE_VIA_LINKEDIN       = 'enable_share_via_linkedin';
	public const FEATURE_SHARE_VIA_WHATSAPP       = 'enable_share_via_whatsapp';
	public const FEATURE_SHARE_VIA_EMAIL          = 'enable_share_via_email';
	public const FEATURE_SHARE_VIA_COPY_CLIPBOARD = 'enable_share_via_copy_clipboard';

	// Block options
	public const FEATURE_DISABLE_AUTOFILLING_RELATED_ARTICLES_BLOCK      = 'enable_not_autofilling_related_articles_block';
	public const FEATURE_DISABLE_DEFAULT_TITLE_HIGHLIGHTED_CONTENT_BLOCK = 'disable_default_title_highlighted_content_block';

	// Article options
	public const FEATURE_ENABLE_MESSAGE_OLD_ARTICLES = 'enable_message_old_articles';

	public function register_hooks(): void {
		add_filter( 'clarkson_context_args', array( $this, 'add_context_args' ) );
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['feature_manager'] = $this;
		return $context;
	}

	/**
	 * Check if feature is enabled via theme options.
	 *
	 * We don't use the get_field function here, because it's only available after the acf/init hook,
	 * while this functionality somtimes needs to be called before that hook.
	 *
	 * @param FeatureManager::FEATURE_* $feature The feature name
	 */
	public function is_enabled( string $feature ): bool {
		return (bool) get_option( sprintf( 'options_%s_%s', Features::META_FEATURES, $feature ), false );
	}
}
