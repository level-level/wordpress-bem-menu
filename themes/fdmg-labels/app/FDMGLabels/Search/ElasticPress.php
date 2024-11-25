<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\PostTypes\Post;
use ElasticPress\Feature\Search\Search;
use ElasticPress\Features;
use WP_Post;
use WP_Query;

use function ElasticPress\Utils\is_integrated_request;

class ElasticPress {
	public function register_hooks(): void {
		add_filter( 'ep_prepare_meta_allowed_keys', array( $this, 'index_meta' ), 10, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_elasticsearch_to_context' ), 10, 2 );
		add_filter( 'ep_post_match_fuzziness', '__return_zero' );
		add_filter( 'ep_highlighting_fields', array( $this, 'highlight_extra_fields' ) );
		add_action( 'ep_highlighting_pre_add_highlight', array( $this, 'replace_post_content_excerpt_highlighting' ) );
		add_filter( 'ep_weighting_default_post_type_weights', array( $this, 'add_rendered_content_to_default_weighting_config' ) );
		add_filter( 'ep_weighting_configuration_for_search', array( $this, 'add_rendered_content_to_weighting_config' ) );
		add_filter( 'ep_search_post_return_args', array( $this, 'return_rendered_content_from_query' ) );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'remove_analyzer_from_query' ), 10, 2 );
	}

	/**
	 * Allow indexing post meta fields.
	 * This is needed for searching content inside the meta fields,
	 * as well as filtering by meta fields.
	 *
	 * @param array<string> $allowed_keys
	 * @param WP_Post $post
	 * @return array<string>
	 */
	public function index_meta( array $allowed_keys, WP_Post $post ): array {
		if ( $post->post_type === Post::POST_TYPE_NAME ) {
			$allowed_keys[] = Post::META_ALTERNATIVE_TITLE;
			$allowed_keys[] = Post::META_AUTHORS;
		}
		return $allowed_keys;
	}

	/**
	 * Highlight extra fields in the search results.
	 * - Alternative title
	 * - Rendered post content
	 *
	 * @param array<string> $fields
	 * @return array<string>
	 */
	public function highlight_extra_fields( array $fields ): array {
		$fields[] = Post::META_ALTERNATIVE_TITLE;
		$fields[] = 'post_content_filtered';
		return $fields;
	}

	/**
	 * If search query is performed, add variable to context which determines if Elasticsearch was used
	 *
	 * @param array<string,mixed> $context
	 * @param WP_Query $query
	 * @return array<string,mixed>
	 */
	public function add_elasticsearch_to_context( array $context, WP_Query $query ): array {
		if ( $query->is_search() ) {
			$context['is_elasticsearch_result'] = $query->elasticsearch_success ?? false;
		}
		return $context;
	}

	/**
	 * Elastic post content excerpt highlighting only works
	 * when a post has no excerpt, and also does not focus on the highlighted content.
	 *
	 * This function replaces the ElasticPress post content excerpt highlighting.
	 *
	 * @return void
	 */
	public function replace_post_content_excerpt_highlighting(): void {
		// Check if the ElasticPress plugin is active
		if ( ! class_exists( '\\ElasticPress\\Features' ) ) {
			return;
		}

		// Check if the ElasticPress Post Search feature is enabled
		/** @var Features */
		$feature_factory = Features::factory();
		$search_feature  = $feature_factory->get_registered_feature( 'search' );
		if ( ! $search_feature instanceof Search ) {
			return;
		}

		if ( ! is_integrated_request( 'highlighting', array( 'public' ) ) ) {
			return;
		}

		// Check if the highlight feature is enabled
		$settings = $search_feature->get_settings();

		if ( ! empty( $settings['highlight_excerpt'] ) && '1' === $settings['highlight_excerpt'] ) {
			remove_filter( 'get_the_excerpt', array( $search_feature, 'ep_highlight_excerpt' ), 10 );
			add_filter( 'get_the_excerpt', array( $this, 'highlight_excerpt_from_content' ), 10, 2 );
		}
	}

	/**
	 * Replicate the ep_highlight_excerpt function from ElasticPress,
	 * but with some changes
	 *
	 * - Use the content instead of the excerpt, if the content contains
	 *   the highlight tag
	 * - When using the content, start the excerpt from the beginning of the
	 *   sentence where the highlight tag is found.
	 */
	public function highlight_excerpt_from_content( string $excerpt, WP_Post $post ): string {
		// Check if the ElasticPress plugin is active
		if ( ! class_exists( '\\ElasticPress\\Features' ) ) {
			return $excerpt;
		}

		// Check if the ElasticPress Post Search feature is enabled
		/** @var Features */
		$feature_factory = Features::factory();
		$search_feature  = $feature_factory->get_registered_feature( 'search' );
		if ( ! $search_feature instanceof Search ) {
			return $excerpt;
		}

		$settings = $search_feature->get_settings();

		// If the excerpt contains the highlighting tag, do nothing
		$current_tag     = $settings['highlight_tag'];
		$highlight_tag   = apply_filters( 'ep_highlighting_tag', $current_tag );
		$highlight_class = apply_filters( 'ep_highlighting_class', 'ep-highlight' );
		$opening_tag     = '<' . $highlight_tag . " class='" . $highlight_class . "'>";
		if ( str_contains( $excerpt, $opening_tag ) ) {
			return $excerpt;
		}

		// Excerpt has nothing highlighted. Use the content instead.
		if ( isset( $post->post_content_filtered ) ) {
			// Use the filtered content if available
			$text = $post->post_content_filtered;
		} else {
			// Use default rendering logic
			$text = get_the_content( '', false, $post );
			$text = strip_shortcodes( $text );
			$text = excerpt_remove_blocks( $text );
			$text = apply_filters( 'the_content', $text );
		}

		$text = str_replace( '\]\]\>', ']]&gt;', $text );
		$text = strip_tags( $text, '<' . esc_html( $highlight_tag ) . '>' );

		// Use the defined length, if already applied...
		$excerpt_length = apply_filters( 'excerpt_length', 55 );

		// Use defined excerpt_more filter if it is used
		$excerpt_more = apply_filters( 'excerpt_more', $text );
		$excerpt_more = $excerpt_more !== $text ? $excerpt_more : '[&hellip;]';

		// If highlight cannot be found in the content, return the excerpt
		$highlight_start_pos = mb_strpos( $text, $opening_tag );
		if ( false === $highlight_start_pos ) {
			return $excerpt;
		}

		// Find end of sentence charsets before $highlight_start
		$end_of_sentence_charsets                                  = array( '.', '!', '?', "\n", '’', '‘' );
		$end_of_sentence_charsets_positions_before_highlight_start = array();
		foreach ( $end_of_sentence_charsets as $end_of_sentence_charset ) {
			$end_of_sentence_pos = mb_strrpos( substr( $text, 0, $highlight_start_pos ), $end_of_sentence_charset );
			if ( false !== $end_of_sentence_pos ) {
				$end_of_sentence_charsets_positions_before_highlight_start[ $end_of_sentence_pos ] = $end_of_sentence_charset;
			}
		}

		if ( $end_of_sentence_charsets_positions_before_highlight_start ) {
			$end_of_sentence_pos_before_highlight_start     = max( array_keys( $end_of_sentence_charsets_positions_before_highlight_start ) );
			$end_of_sentence_charset_before_highlight_start = $end_of_sentence_charsets_positions_before_highlight_start[ $end_of_sentence_pos_before_highlight_start ];

			// Start the text at the word before the highlight start position
			$text = ltrim( mb_substr( $text, $end_of_sentence_pos_before_highlight_start + mb_strlen( $end_of_sentence_charset_before_highlight_start ) ) );
		} else {
			// Start the text from the beginning. It should be the first sentence where the word is in.
			$text = ltrim( $text );
		}

		/**
		 * WordPress would handle this using `wp_trim_words` but that removes all tags,
		 * including the one used to highlight the search term.
		 */
		$words = explode( ' ', $text, $excerpt_length + 1 );
		if ( count( $words ) > $excerpt_length ) {
			array_pop( $words );
			array_push( $words, $excerpt_more );
			$text = implode( ' ', $words );
		}

		return $text;
	}

	/**
	 * Add the post_content_filtered to the custom weight configuration.
	 * This will allow the rendered post content to be used when searching.
	 *
	 * @see https://www.elasticpress.io/documentation/article/how-to-search-using-rendered-content-shortcodes-and-reusable-blocks/
	 *
	 * @param array<array<string,mixed>> $weight_config
	 * @return array<array<string,mixed>>
	 */
	public function add_rendered_content_to_weighting_config( array $weight_config ): array {
		foreach ( $weight_config as &$post_type_config ) {
			if ( isset( $post_type_config['post_content'] ) ) {
				$post_type_config['post_content_filtered'] = $post_type_config['post_content'];
			}
		}
		return $weight_config;
	}

	/**
	 * Add the post_content_filtered to the default weight configuration.
	 * This will allow the rendered post content to be used when searching.
	 *
	 * @param array<string,mixed> $default_weighting_config
	 * @return array<string,mixed>
	 */
	public function add_rendered_content_to_default_weighting_config( array $default_weighting_config ): array {
		if ( ! isset( $default_weighting_config['post_content'] ) ) {
			return $default_weighting_config;
		}

		$default_weighting_config['post_content_filtered'] = $default_weighting_config['post_content'];
		return $default_weighting_config;
	}

	/**
	 * Include the rendered post content in the search results posts objects.
	 * This will allow the rendered post content to be used for highlighting.
	 *
	 * @param array<string> $args
	 * @return array<string>
	 */
	public function return_rendered_content_from_query( array $args ): array {
		$args[] = 'post_content_filtered';
		return $args;
	}

	/**
	 * Remove the analyzer from the query if present. It does fuzzy searching
	 * even if fuzzy is disabled.
	 *
	 * @param array<string,mixed> $query
	 * @return array<string,mixed>
	 */
	public function remove_analyzer_from_query( array $query, array $query_vars ): array {
		if ( empty( $query['bool']['should'] ) ) {
			return $query;
		}

		foreach ( $query['bool']['should'] as &$should ) {
			if ( isset( $should['multi_match']['analyzer'] ) ) {
				unset( $should['multi_match']['analyzer'] );
			}
		}
		return $query;
	}
}
