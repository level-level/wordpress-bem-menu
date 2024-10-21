<?php

namespace FDMGLabels\RSSFeed;

use Clarkson\Theme\ACF;
use Clarkson_Core\Objects;
use Clarkson_Core\Templates;

class MediaItemRSSFeed {
	public function register_hooks(): void {
		add_action( 'rss2_ns', array( $this, 'add_media_namespaces' ) );
		add_action( 'rss2_item', array( $this, 'add_media_thumbnail_to_rss_feed' ) );
	}

	public function add_media_namespaces(): void {
		Templates::get_instance()->echo_twig( 'partials/feeds/namespaces.twig', array(), true );
	}

	public function add_media_thumbnail_to_rss_feed(): void {
		$post = get_post();
		if ( ! $post instanceof \WP_Post ) {
			return;
		}

		Templates::get_instance()->echo_twig(
			'partials/feeds/media-property.twig', array(
				'object' => Objects::get_instance()->get_object( $post ),
			), true
		);
	}
}
