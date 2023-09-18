<?php

namespace Clarkson_Core\WordPress_Object;

use Clarkson\Theme\PostTypes\Post as PostTypesPost;
use Clarkson_Core\Templates;
use FDMGLabels\Comments\CommentWalker;
use LevelLevel\Paywall\ContentExtractor;

class post extends base_object {
	public static $type = PostTypesPost::POST_TYPE_NAME;

	public function get_alternative_title(): string {
		return get_field( PostTypesPost::META_ALTERNATIVE_TITLE, $this->get_id() ) ?: '';
	}

	public function get_authors(): array {
		return get_field( PostTypesPost::META_AUTHORS, $this->get_id() ) ?: array();
	}

	public function has_paywall_block(): bool {
		if ( ! class_exists( '\\LevelLevel\\Paywall\\ContentExtractor' ) ) {
			return false;
		}

		return ( new ContentExtractor() )->content_has_paywall_block( $this->get_object()->post_content );
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
		);

		$html = wp_list_comments(
			array(
				'walker' => new CommentWalker(),
				'style'  => 'ol',
			), get_comments( $args )
		);
		return is_string( $html ) ? $html : '';
	}
}
