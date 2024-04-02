<?php
namespace FDMGLabels;

class EmbedCacheRemover {
	public function register_hooks(): void {
		add_action( 'transition_post_status', array( $this, 'on_post_publish' ), 10, 3 );
	}

	/**
	 * Remove oEmbed data from post meta when a post is published.
	 *
	 * @param string $new_status
	 * @param string $old_status
	 * @param \WP_Post $post
	 */
	public function on_post_publish( $new_status, $old_status, $post ): void {
		global $wpdb;

		if ( 'publish' !== $new_status || 'publish' === $old_status ) {
			return;
		}

		$post_id = $post->ID;

		$like      = '_oembed_%';
		$meta_keys = $wpdb->get_col(
			$wpdb->prepare(
				"SELECT meta_key FROM {$wpdb->postmeta} WHERE post_id = %d AND meta_key LIKE %s",
				$post_id, $wpdb->esc_like( $like )
			)
		);

		foreach ( $meta_keys as $meta_key ) {
			delete_post_meta( $post_id, $meta_key );
		}
	}
}
