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
		add_filter( 'ep_highlighting_fields', array( $this, 'highlight_alternative_title' ) );
		add_action( 'ep_highlighting_pre_add_highlight', array( $this, 'replace_post_content_excerpt_highlighting' ) );
		add_filter( 'ep_weighting_configuration_for_search', array( $this, 'add_rendered_content_to_weighting_config' ) );
		add_filter( 'ep_search_fields', array( $this, 'add_rendered_content_as_search_field' ) );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'remove_analyzer_from_query' ), 10, 2 );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'search_rendered_post_content' ), 10, 2 );
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
	 * Highlight the alternative title field in the search results.
	 *
	 * @param array<string> $fields
	 * @return array<string>
	 */
	public function highlight_alternative_title( array $fields ): array {
		$fields[] = Post::META_ALTERNATIVE_TITLE;
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
	 * Highlight the provided input string based on the search query.
	 *
	 * When ElasticPress Post Search feature is enabled, this function will
	 * highlight the search query (or parts of it) in the input string.
	 *
		 * ElasticPress does highlight the search result string in the post title and excerpt by itself.
	 * This feature exists to also do this with other texts, like the alternative title field.
	 *
		 * Please note that this function is custom built and can behave differently (mark less words) than the default feature of ElasticPress.
	 *
	 * @param string $input_string The string to highlight.
	 * @param string $search_query The search query.
	 * @param string $input_string_identifier A self-made-up identifier of the input string. Must be unique for each input string.
	 * @return string
	 */
	public function highlight_search_result_string( string $input_string, string $search_query, string $input_string_identifier ): string {
		// Check if search query is empty then highlight nothing
		if ( empty( $search_query ) ) {
			return $input_string;
		}

		// Check if the ElasticPress plugin is active
		if ( ! class_exists( '\\ElasticPress\\Features' ) ) {
			return $input_string;
		}

		// Check if the ElasticPress Post Search feature is enabled
		/** @var Features */
		$feature_factory = Features::factory();
		$search_feature  = $feature_factory->get_registered_feature( 'search' );
		if ( ! $search_feature instanceof Search ) {
			return $input_string;
		}

		// Check if the highlight feature is enabled
		$settings = $search_feature->get_settings();
		if ( '1' !== $settings['highlight_enabled'] ) {
			return $input_string;
		}

		// Get the highlight tag and class
		$current_tag     = $settings['highlight_tag'];
		$highlight_tag   = apply_filters( 'ep_highlighting_tag', $current_tag );
		$highlight_class = apply_filters( 'ep_highlighting_class', 'ep-highlight' );
		$opening_tag     = '<' . $highlight_tag . " class='" . $highlight_class . "'>";
		$closing_tag     = '</' . $highlight_tag . '>';

		// Prepare the strings to highlight
		$strings_to_highlight = array( $search_query );
		$strings_to_highlight = array_merge( $strings_to_highlight, explode( ' ', $search_query ) );

		// Search the input string. If a $strings_to_highlight element is found, replace it with the highlighted version. Only replace a maximum of $max_number_of_highlights times, and only replace if it's not wrapped by the $opening_tag and $closing_tag.
		$highlighted_string       = $input_string;
		$number_of_highlights     = 0;
		$max_number_of_highlights = apply_filters( 'ep_highlight_number_of_fragments', 0, $input_string_identifier );
		foreach ( $strings_to_highlight as $string_to_highlight ) {
			// If the maximum number of highlights is reached, stop the loop
			if ( $max_number_of_highlights > 0 && $number_of_highlights >= $max_number_of_highlights ) {
				break;
			}

			// Wrap the $string_to_highlight with the $opening_tag and $closing_tag if it's not already wrapped by them
			$escaped_string_to_highlight    = preg_quote( $string_to_highlight, '/' );
			$escaped_opening_tag            = preg_quote( $opening_tag, '/' );
			$escaped_closing_tag            = preg_quote( $closing_tag, '/' );
			$replacement                    = $opening_tag . '$1' . $closing_tag;
			$pattern                        = '/(?!' . $escaped_opening_tag . '.*?)(' . $escaped_string_to_highlight . ')(?!.*?' . $escaped_closing_tag . ')/i';
			$highlighted_string_replacement = preg_replace( $pattern, $replacement, $highlighted_string, 1 );
			$highlighted_string             = $highlighted_string_replacement ? $highlighted_string_replacement : $highlighted_string;
		}
		return $highlighted_string;
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
		$text = get_the_content( '', false, $post );

		$text = strip_shortcodes( $text );
		$text = excerpt_remove_blocks( $text );

		$text = apply_filters( 'the_content', $text );
		$text = str_replace( '\]\]\>', ']]&gt;', $text );

		$text = strip_tags( $text, '<' . esc_html( $highlight_tag ) . '>' );

		// use the defined length, if already applied...
		$excerpt_length = apply_filters( 'excerpt_length', 55 );

		// use defined excerpt_more filter if it is used
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
	 * Add the post_content_filtered to the weight configuration.
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
				unset( $post_type_config['post_content'] );
			}
		}
		return $weight_config;
	}

	/**
	 * Add the post_content_filtered to the search fields.
	 * This will allow the rendered post content to be used when searching.
	 *
	 * @param array<string> $search_fields
	 * @return array<string>
	 */
	public function add_rendered_content_as_search_field( array $search_fields ): array {
		foreach ( $search_fields as $key => $field ) {
			$field_parts = explode( '^', $field );
			if ( 'post_content' === $field_parts[0] ) {
				$field_parts[0] = 'post_content_filtered';
				$search_fields[ $key ] = implode( '^', $field_parts );
				break;
			}
		}

		return $search_fields;
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

	public function search_rendered_post_content( array $query, array $query_vars ): array {
		if ( empty( $query['bool']['should'] ) ) {
			return $query;
		}

		foreach ( $query['bool']['should'] as &$should ) {
			if ( ! isset( $should['multi_match']['fields'] ) ) {
				continue;
			}

			$fields = $should['multi_match']['fields'];
			foreach ( $fields as &$field ) {
				$field_parts = explode( '^', $field );
				if ( 'post_content' === $field_parts[0] ) {
					$field_parts[0] = 'post_content_filtered';
					$field = implode( '^', $field_parts );
					break;
				}
			}
		}
		return $query;
	}
}
