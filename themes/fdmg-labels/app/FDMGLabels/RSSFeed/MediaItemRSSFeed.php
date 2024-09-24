<?php

namespace FDMGLabels\RSSFeed;

class MediaItemRSSFeed{
	public function register_hooks(): void {
		add_action( 'rss2_item', array( $this, 'add_media_thumbnail_to_rss_feed' ) );
	}

	public function add_media_thumbnail_to_rss_feed(){
		global $post;
		if ( ! has_post_thumbnail( $post->ID ) ) {
			return;
		}
		$thumbnail_id = get_post_thumbnail_id( $post->ID );
		$thumbnail = get_attachment_link( $thumbnail_id );

		$thumbnail = get_the_post_thumbnail( $post->ID, array( '512', '288' ) );
		echo "<image>{$thumbnail}</image>";
	}
}
