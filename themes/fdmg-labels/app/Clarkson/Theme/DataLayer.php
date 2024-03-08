<?php
namespace Clarkson\Theme;

use Clarkson\Theme\Taxonomies\Category;
use Clarkson_Core\WordPress_Object\Clarkson_Term;
use Exception;
use WP_Post;

class DataLayer {
	public function register_hooks(): void {
		add_filter( 'clarkson_core_template_context', array( $this, 'add_context_args' ) );
	}

	public const EVENT_TEASER   = 'TEASER';
	public const EVENT_PAGEVIEW = 'PAGEVIEW';
	public const EVENT_SHARE    = 'SOCIAL_SHARE';

	/**
	 * get_post_config
	 *
	 * @return array
	 */
	public function get_post_config( $post ): array {
		$config = array(
			'event'        => self::EVENT_PAGEVIEW,
			'id'           => $post->get_id(),
			'title'        => $post->get_title(),
			'page_type'    => 'article',
			'page_subtype' => 'article detail page',
		);

		return $config;
	}

	/**
	 * get archive config
	 *
	 * @return array<mixed>
	 */
	public function get_archive_config(): array {
		$archive_title = single_cat_title( '', false ) ? single_cat_title( '', false ) : get_the_archive_title();
		$page_subtype  = ( is_category() || is_tax() ) ? $archive_title : get_the_archive_title();
		$config        = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $archive_title,
			'page_category' => 'archive',
			'page_type'     => 'category',
			'page_subtype'  => $page_subtype,
		);

		return $config;
	}

	/**
	 * get frontpage config
	 *
	 * @return array<mixed>
	 */
	public function get_front_page_config(): array {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => 'homepage',
			'page_category' => 'home',
			'page_type'     => 'homepage',
			'page_subtype'  => 'homepage',
		);

		return $config;
	}

	/**
	 * get home config
	 *
	 * @return array<mixed>
	 */
	public function get_home_config(): array {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => 'Latest news',
			'page_category' => 'latest-news',
			'page_type'     => 'latest news',
			'page_subtype'  => 'latest news archive',
		);

		return $config;
	}

	/**
	 * get search config
	 *
	 * @return array<mixed>
	 */
	public function get_search_config(): array {
		$query  = get_search_query();
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $query,
			'page_category' => 'search',
			'page_type'     => 'search-results',
			'page_subtype'  => 'search-results',
		);

		return $config;
	}

	/**
	 * get page config
	 *
	 * @return array<mixed>
	 */
	public function get_page_config( $post ): array {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $post->get_title(),
			'page_category' => 'page',
			'page_type'     => 'page',
			'page_subtype'  => $post->get_title(),
		);

		return $config;
	}

	/**
	 * get partner config
	 *
	 * @return void
	 */
	public function get_partner_config( $post ) {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $post->get_title(),
			'page_category' => 'partner',
			'page_type'     => 'partner detail page',
			'page_subtype'  => 'partner',
		);

		return $config;
	}

	/**
	 * get vacancy config
	 *
	 * @return array<mixed>
	 */
	public function get_vacancy_config( $post ): array {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $post->get_title(),
			'page_category' => 'vacancy',
			'page_type'     => 'vacancy detail page',
			'page_subtype'  => 'vacancy',
		);

		return $config;
	}

	/**
	 * get team config
	 *
	 * @param WP_Post $post
	 * @return array<mixed>
	 */
	public function get_team_config( $post ): array {
		$config = array(
			'event'         => self::EVENT_PAGEVIEW,
			'title'         => $post->get_title(),
			'page_category' => 'team',
			'page_type'     => 'team detail page',
			'page_subtype'  => 'team',
		);

		return $config;
	}

	/**
	 * get teaser config
	 *
	 * @return array
	 */
	public function get_teaser_config( $post, $component_subtype ): array {
		$revisions = wp_get_latest_revision_id_and_total_count( $post->get_id() );

		$config = array(
			'event'             => self::EVENT_TEASER,
			'post_type'         => $post->get_post_type(),
			'id'                => $post->get_id(),
			'title'             => $post->get_title(),
			'url'               => $post->get_permalink(),
			'categories'        => implode( ', ', array_map( fn( Clarkson_Term $category ) => $category->get_name(), $post->get_terms( Category::TAXONOMY_NAME ) ) ),
			'author'            => $post->get_author()->get_display_name(),
			'publication_date'  => $post->get_date_i18n( 'Y-m-d' ),
			'publication_time'  => $post->get_date_i18n( 'H:i' ),
			'revisions_count'   => $revisions['count'],
			'component_subtype' => $component_subtype,
		);

		return $config;
	}

	/**
	 * get share config
	 *
	 * @param string $type
	 * @return void
	 */
	public function get_share_config( $post, $type ) {
		$config = array(
			'event'      => self::EVENT_SHARE,
			'id'         => $post->get_id(),
			'share_type' => $type,
		);

		return $config;
	}

	/**
	 * Get config
	 *
	 * @param string $type
	 * @param boolean $component_subtype
	 * @return void
	 */
	public function get_config( $post, $type, $component_subtype = false ): void {
		$config = array();

		if ( $type === self::EVENT_TEASER ) {
			$config = $this->get_teaser_config( $post, $component_subtype );
		} elseif ( $type === self::EVENT_PAGEVIEW ) {
			if ( is_front_page() ) {
				$config = $this->get_front_page_config( $post );
			} elseif ( is_home() ) {
				$config = $this->get_home_config( $post );
			} elseif ( is_search() ) {
				$config = $this->get_search_config( $post );
			} elseif ( is_archive() ) {
				$config = $this->get_archive_config( $post );
			} elseif ( is_page() ) {
				$config = $this->get_page_config( $post );
			} elseif ( is_singular( 'ii_partner' ) ) {
				$config = $this->get_partner_config( $post );
			} elseif ( is_singular( 'ii_vacancy' ) ) {
				$config = $this->get_vacancy_config( $post );
			} elseif ( is_singular( 'ii_team' ) ) {
				$config = $this->get_team_config( $post );
			} else {
				$config = $this->get_post_config( $post );
			}
		} elseif ( $type === self::EVENT_SHARE ) {
			$config = $this->get_share_config( $post, $component_subtype );
		} else {
			throw new Exception( 'Invalid data layer event type' );
		}

		echo 'data-datalayer="' . esc_attr( wp_json_encode( $config ) ) . '"';
	}

	/**
	 * @param array<string,mixed> $context
	 * @return array<string,mixed>
	 */
	public function add_context_args( array $context ): array {
		$context['datalayer'] = $this;
		return $context;
	}
}
