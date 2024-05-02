<?php
namespace FDMGLabels\Search;

use Clarkson\Theme\PostTypes\Post;
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
}
