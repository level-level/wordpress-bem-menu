<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\ACFPages\Features;
use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\Templates;
use FDMGLabels\Comments\CommentWalker;
use FDMGLabels\FeatureManager;
use LevelLevel\Paywall\ContentExtractor;

class post extends base_object {
	public static $type = PostTypesPost::POST_TYPE_NAME;

	public function get_alternative_title(): string {
		return get_field( PostTypesPost::META_ALTERNATIVE_TITLE, $this->get_id() ) ?: '';
	}

	/**
	 * @return ii_team[]
	 */
	public function get_authors(): array {
		return get_field( PostTypesPost::META_AUTHORS, $this->get_id() ) ?: array();
	}

	public function has_paywall_block(): bool {
		if ( ! class_exists( ContentExtractor::class ) ) {
			return false;
		}

		return ( new ContentExtractor() )->content_has_paywall_block( $this->get_object()->post_content );
	}

	public function get_unpaywalled_content(): string {
		global $post;
		if ( ! class_exists( ContentExtractor::class ) ) {
			return $this->get_content();
		}

		$content_extractor       = new ContentExtractor();
		$postclone               = clone $this->_post;
		$postclone->post_content = $content_extractor->remove_paywall_block( $postclone->post_content );

		setup_postdata( $postclone );

		// Post stays empty when wp_query 404 is set, resulting in a warning from the_content.
		if ( null === $post ) {
			$post = $postclone; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		}

		ob_start();
		the_content();

		$content = ob_get_clean() ?: '';
		wp_reset_postdata();

		return $content;
	}

	public function has_comments_enabled(): bool {
		return comments_open( $this->get_id() );
	}

	public function get_comments_count(): int {
		$comments = wp_count_comments( $this->get_id() );
		return $comments->approved;
	}

	public function display_comments(): string {
		$cpage = get_query_var( 'cpage' ) ? get_query_var( 'cpage' ) : 1;
		$args  = array(
			'post_id'           => $this->get_id(),
			'orderby'           => 'comment_date_gmt',
			'order'             => 'ASC',
			'status'            => 'approve',
			'number'            => (int) get_option( 'comments_per_page' ),
			'paged'             => $cpage,
			'reverse_top_level' => get_option( 'default_comments_page' ) === 'oldest' ? false : true,
			'comment_type' 	    => 'comment',
		);

		$html = wp_list_comments(
			array(
				'walker' => new CommentWalker(),
				'style'  => 'ol',
			), get_comments( $args )
		);
		return is_string( $html ) ? $html : '';
	}

	/**
	 * Show message on articles if it's older then the defined date.
	 */
	public function get_old_article_message(): string {
		$is_enabled = (bool) get_option( sprintf( 'options_%s_%s', Features::META_FEATURES, FeatureManager::FEATURE_ENABLE_MESSAGE_OLD_ARTICLES ), false );

		if ( ! $is_enabled ) {
			return '';
		}

		$date    = get_option( sprintf( 'options_%s_%s_%s', Features::META_FEATURES, Features::META_MESSAGE_OLD_ARTICLES, Features::META_DATE ), false );
		$message = get_option( sprintf( 'options_%s_%s_%s', Features::META_FEATURES, Features::META_MESSAGE_OLD_ARTICLES, Features::META_MESSAGE ), false );

		$publish_date = $this->get_publish_date( 'YYYYMMDD' );

		$article_date     = strtotime( $publish_date['datetime'] );
		$defined_old_date = strtotime( $date );

		if ( $article_date > $defined_old_date ) {
			return '';
		}

		return $message;
	}
}
