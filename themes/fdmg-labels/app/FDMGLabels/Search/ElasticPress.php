<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\ACFBlocks\Advertisement;
use Clarkson\Theme\ACFBlocks\ArticlesInColumns;
use Clarkson\Theme\ACFBlocks\FeaturedArticle;
use Clarkson\Theme\ACFBlocks\InlineLink;
use Clarkson\Theme\ACFBlocks\Partners;
use Clarkson\Theme\ACFBlocks\RelatedArticles;
use Clarkson\Theme\ACFBlocks\Team;
use Clarkson\Theme\PostTypes\Post;
use ElasticPress\Feature\Search\Search;
use ElasticPress\Features;
use LevelLevel\Paywall\ACFBlock;
use WP_Post;
use WP_Query;

use function ElasticPress\Utils\is_integrated_request;

class ElasticPress {

	private const DEFAULT_DECAY_SCALE = '1460d';

	public function register_hooks(): void {
		add_filter( 'ep_prepare_meta_allowed_keys', array( $this, 'index_meta' ), 10, 2 );
		add_filter( 'clarkson_core_template_context', array( $this, 'add_elasticsearch_to_context' ), 10, 2 );
		add_filter( 'ep_post_match_fuzziness', '__return_zero' );
		add_filter( 'ep_highlighting_fields', array( $this, 'highlight_extra_fields' ) );
		add_action( 'ep_highlighting_pre_add_highlight', array( $this, 'replace_post_content_excerpt_highlighting' ) );
		add_filter( 'ep_weighting_default_post_type_weights', array( $this, 'add_rendered_content_to_default_weighting_config' ) );
		add_filter( 'ep_weighting_configuration_for_search', array( $this, 'add_rendered_content_to_weighting_config' ) );
		add_filter( 'ep_search_post_return_args', array( $this, 'return_rendered_content_from_query' ) );
		add_filter( 'ep_post_sync_args_post_prepare_meta', array( $this, 'exclude_blocks_from_content' ) );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'remove_analyzer_from_query' ) );
		add_filter( 'ep_default_search_analyzer_filters', array( $this, 'remove_snowball_stem' ) );
		add_filter( 'ep_default_analyzer_filters', array( $this, 'remove_snowball_stem' ) );
		add_filter( 'ep_analyzer_language', array( $this, 'limit_stopwords_list' ), 11, 2 );
		add_filter( 'epwr_offset', array( $this, 'set_offset' ) );
		add_filter( 'epwr_scale', array( $this, 'set_scale' ) );
		add_filter( 'epwr_decay', array( $this, 'set_decay' ) );
		add_filter( 'epwr_decay_function', array( $this, 'set_decay_function' ) );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'boost_phrase_match' ) );
		add_filter( 'ep_post_formatted_args_query', array( $this, 'maybe_remove_non_phrase_searches' ), 10, 2 );
		add_filter( 'ep_feature_settings_schema', array( $this, 'add_settings' ), 10, 2 );
	}

	/**
	 * Boost the phrase match in the search query.
	 *
	 * We want the phrase match results to always drift to the top,
	 * as there are certain phrases that carry specific meaning.
	 * @example: 'netto pensioen' should always be more relevant than 'netto' and 'pensioen' separately.
	 *
	 * @see https://app.asana.com/0/0/1208464277170192/1208517346683054/f
	 *
	 * @param array<string,mixed> $query
	 * @return array<string,mixed>
	 */
	public function boost_phrase_match( array $query ): array {
		if ( empty( $query['bool']['should'] ) ) {
			return $query;
		}

		foreach ( $query['bool']['should'] as &$should ) {
			if ( isset( $should['multi_match']['query'] ) && $should['multi_match']['type'] === 'phrase' ) {
				$should['multi_match']['boost'] = 9999;
			}
		}
		return $query;
	}


	/**
	 * Remove cross-fields searches from the query.
	 *
	 * Cross-fields searches are searches that search for a phrase in multiple fields.
	 * This is not desired, as it can lead to unexpected results.
	 * For example, searching for 'netto pensioen' in the title and content fields
	 * would also return results where 'netto' is in the title and 'pensioen' is in the content.
	 *
	 * @see https://app.asana.com/0/0/1208464277170192/1208517346683054/f
	 *
	 * @param array<string,mixed> $query
	 * @param array<string,mixed> $query_vars
	 * @return array<string,mixed>
	 */
	public function maybe_remove_non_phrase_searches( array $query, array $query_vars ): array {
		if ( empty( $query['bool']['should'] ) ) {
			return $query;
		}

		$query['bool']['should'] = array_filter(
			$query['bool']['should'], function ( array $should ): bool {
				return $should['multi_match']['type'] !== 'cross_fields';
			}
		);

		return $query;
	}

	/**
	 * Add decay scale options to the ElasticPress settings.
	 *
	 * @param array<string,mixed> $schema
	 * @return array<string,mixed>
	 */
	public function add_settings( array $schema, string $slug ): array {
		if ( 'search' !== $slug ) {
			return $schema;
		}

		$option = array(
			'default' => self::DEFAULT_DECAY_SCALE,
			'type'    => 'string',
			'key'     => 'fdmg_decay_scale',
			'label'   => __( 'Decay scale', 'fdmg-labels' ),
			'help'    => __( 'How long it takes for a post to lose 50% of it\'s score. Examples: 365d. Default: 1460d', 'fdmg-labels' ),
		);

		array_splice( $schema, 2, 0, array( $option ) );

		return $schema;
	}


	/**
	 * Set the offset for the search query.
	 */
	public function set_offset(): string {
		return '1d';
	}

	/**
	 * Set the decay for the search query.
	 */
	public function set_decay(): string {
		return '0.5';
	}

	/**
	 * Set the scale for the search query.
	 */
	public function set_scale(): string {
		if ( ! class_exists( Features::class ) ) {
			return self::DEFAULT_DECAY_SCALE;
		}

		$feature_factory = Features::factory();
		if ( ! $feature_factory instanceof Features ) {
			return self::DEFAULT_DECAY_SCALE;
		}

		$feature = $feature_factory->get_registered_feature( 'search' );
		if ( ! $feature instanceof Search ) {
			return self::DEFAULT_DECAY_SCALE;
		}
		return $feature->get_setting( 'fdmg_decay_scale' ) ?? self::DEFAULT_DECAY_SCALE;
	}

	/**
	 * Set the decay function for the search query.
	 */
	public function set_decay_function(): string {
		return 'linear';
	}

	/**
	 * Remove the snowball stem filter from the default analyzer filters.
	 *
	 * See https://app.asana.com/0/1206507323673111/1208464277170192/f
	 *
	 * @param array<string> $filters
	 * @return array<string>
	 */
	public function remove_snowball_stem( array $filters ): array {
		$filters = array_filter(
			$filters, function ( string $filter ): bool {
				return $filter !== 'ewp_snowball';
			}
		);
		return $filters;
	}

	/**
	 * Limit the stopwords list for the search analyzer.
	 *
	 * @see https://www.elastic.co/guide/en/elasticsearch/guide/current/using-stopwords.html
	 * @see https://app.asana.com/0/0/1209349785420643/1209506408269113/f
	 *
	 * @return string|string[] // Language or list of stopwords
	 */
	public function limit_stopwords_list( string $language, string $context ): mixed {
		if ( $context !== 'filter_ep_stop' ) {
			return $language;
		}

		if ( $language !== '_dutch_' ) {
			return $language;
		}

		return array(
			'de',
			'en',
			'van',
			'te',
			'dat',
			'die',
			'in',
			'een',
			'het',
			'op',
			'met',
			'als',
			'er',
			'om',
			'of',
			'dit',
			'tot',
			'uit',
			'der',
		);
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
		if ( ! class_exists( Features::class ) ) {
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
		if ( ! class_exists( Features::class ) ) {
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
		if ( ! empty( $post->post_content_filtered ) ) {
			// Use the filtered content if available
			$text = $post->post_content_filtered;
		} else {
			// Use the excerpt generated by the content, which is the default that ElasticSearch does
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
	 * Get the blocks that should be excluded from the indexed content
	 *
	 * @return array<string>
	 */
	private function get_excluded_blocks(): array {
		$blocks = array(
			sprintf( 'acf/block-%s', Advertisement::BLOCK_NAME ),
			sprintf( 'acf/block-%s', ArticlesInColumns::BLOCK_NAME ),
			sprintf( 'acf/block-%s', FeaturedArticle::BLOCK_NAME ),
			sprintf( 'acf/block-%s', Partners::BLOCK_NAME ),
			sprintf( 'acf/block-%s', RelatedArticles::BLOCK_NAME ),
			sprintf( 'acf/block-%s', Team::BLOCK_NAME ),
			sprintf( 'acf/block-%s', InlineLink::BLOCK_NAME ),
		);

		require_once ABSPATH . '/wp-admin/includes/plugin.php';
		if ( is_plugin_active( 'll-paywall/ll-paywall.php' ) ) {
			$blocks[] = ACFBlock::ACF_GENERATED_BLOCK_NAME;
		}

		return $blocks;
	}

	/**
	 * Exclude content blocks that should not be indexed in ElasticSearch
	 *
	 * @param array<string,mixed> $args
	 * @return array<string,mixed>
	 */
	public function exclude_blocks_from_content( array $args ): array {
		if ( empty( $args['post_content'] ) ) {
			return $args;
		}

		// Loop blocks. If a block is excluded, remove it from the blocks list
		$excluded_blocks = $this->get_excluded_blocks();
		$parsed_blocks   = parse_blocks( $args['post_content'] );
		foreach ( $parsed_blocks as $block_index => $block ) {
			if ( in_array( $block['blockName'], $excluded_blocks, true ) ) {
				unset( $parsed_blocks[ $block_index ] );
			}
		}

		// Rebuild the blocks list
		$serialized_blocks    = serialize_blocks( $parsed_blocks );
		$args['post_content'] = $serialized_blocks;

		// If there is filtered content, regenerate it based on the new blocks
		if ( ! empty( $args['post_content_filtered'] ) ) {
			$args['post_content_filtered'] = apply_filters( 'the_content', $args['post_content'] );
		}
		return $args;
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
	public function remove_analyzer_from_query( array $query ): array {
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
