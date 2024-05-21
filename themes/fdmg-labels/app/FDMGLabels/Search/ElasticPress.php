<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\PostTypes\Post;
use ElasticPress\Feature\Search\Search;
use ElasticPress\Features;
use WP_Post;
use WP_Query;

class ElasticPress {
	public function register_hooks(): void {
		add_filter( 'ep_prepare_meta_allowed_keys', array( $this, 'index_meta' ), 10, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_elasticsearch_to_context' ), 10, 2 );
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
	 * @param string $input_string The string to highlight.
	 * @param string $search_query The search query.
	 * @param string $input_string_identifier A self-made-up identifier of the input string. Must be unique for each input string.
	 * @return string
	 */
	public function highlight_search_result_string( string $input_string, string $search_query, string $input_string_identifier ): string {
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

			// Highlight the string
			$highlighted_string_replacement = preg_replace_callback(
				'/' . preg_quote( $string_to_highlight, '/' ) . '/i',
				function ( $matches ) use ( $opening_tag, $closing_tag, &$number_of_highlights ) {
					$number_of_highlights++;
					return $opening_tag . $matches[0] . $closing_tag;
				},
				$highlighted_string
			);
			$highlighted_string             = $highlighted_string_replacement ? $highlighted_string_replacement : $highlighted_string;
		}
		return $highlighted_string;
	}
}
