<?php
namespace Clarkson\Theme;

use Clarkson_Core\Templates;

class OEmbed {
	public function register_hooks(): void {
		add_filter( 'embed_oembed_html', array( $this, 'rewrite_oembed' ), 99, 2 );
		add_action( 'acf/init', array( $this, 'remove_acf_oembed_format_value_filter' ), 1 );
		add_filter( 'acf/format_value/type=oembed', array( $this, 'cache_acf_oembed' ), 99 );
	}

	/**
	 * Rewrites oEmbed videos into GDPR-compliant embeds.
	 *
	 * @param string $html The cached HTML result, stored in post meta.
	 * @param string $url The attempted embed URL.
	 *
	 * @return string
	 */
	public function rewrite_oembed( string $html, string $url ): string {
		$oembed       = _wp_oembed_get_object();
		$provider_url = $oembed->get_provider( $url ) ?: $url;
		$source       = $this->get_source( $provider_url );
		$is_video     = $this->is_video_embed( $source );
		$is_audio     = $this->is_audio_embed( $source );
		$thumbnail    = '';
		$html         = str_replace( '<iframe', '<iframe loading="lazy"', $html );

		if ( $is_video ) {
			$thumbnail = $this->get_thumbnail( $source, $url );
		}

		// Lazyload video & audio embeds
		if ( $is_video || $is_audio ) {
			$html = str_replace( 'src', 'data-src', $html );
		}

		$type = $source;
		if ( $is_video ) {
			$type = 'video';
		} elseif ( $is_audio ) {
			$type = 'audio';
		}

		$args = array(
			'html'           => $html,
			'source'         => $source,
			'type'           => $type,
			'thumbnail'      => $thumbnail,
			'notice_text'    => __( 'This content is available after accepting the cookies.', 'impact-investor' ),
			'button_text'    => __( 'I agree', 'impact-investor' ),
			'disable_notice' => $this->maybe_disable_notice( $source, $html ),
		);

		$options = get_field( 'theme_options', 'option' );

		if ( ! empty( $options ) && $options['enable_liveramp_cmp'] ) {
			$args['notice_text'] = __( 'This content is available after accepting the social media cookies.', 'impact-investor' );
			$args['button_text'] = __( 'Change cookie-settings', 'impact-investor' );
		}

		return Templates::get_instance()->render_twig( 'partials/components/oembeds/oembed.twig', $args, true );
	}

	/**
	 * Remove 'acf/format_value/type=oembed' filter
	 */
	public function remove_acf_oembed_format_value_filter(): void {
		$field_type = acf_get_field_type( 'oembed' );
		remove_filter( 'acf/format_value/type=oembed', array( $field_type, 'format_value' ) );
	}

	/**
	 * Force acf to cache oembeds
	 * @param string $value The oEmbed URL
	 */
	public function cache_acf_oembed( string $value ): string {
		if ( is_admin() ) {
			return wp_oembed_get( $value ) ?: '';
		}

		global $wp_embed;
		return $wp_embed->shortcode( array(), $value );
	}

	/**
	 * Get the ID of the video from the embed provider.
	 */
	public function get_embed_id( string $source, string $url ): string {
		$matches = array();

		if ( $source === 'vimeo' ) {
			if ( preg_match( '%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $url, $matches ) ) {
				return $matches[3];
			}
		}

		if ( $source === 'youtube' ) {
			if ( preg_match( "#(?<=v=)[a-zA-Z0-9-]+(?=&)|(?<=v\/)[^&\n]+|(?<=v=)[^&\n]+|(?<=youtu.be/)[^&\n]+#", $url, $matches ) ) {
				return $matches[0];
			}
		}

		return '';
	}

	/**
	 * Get embed source from embed url
	 */
	public function get_source( string $url ): string {
		$host  = wp_parse_url( $url, PHP_URL_HOST );
		$parts = explode( '.', $host );
		$parts = array_reverse( $parts );

		if ( strlen( $parts[1] ) <= 2 ) {
			return $parts[2] ?? $parts[1];
		}

		return $parts[1];
	}

	/**
	 * Get the response code of a specific URL.
	 *
	 * @return int|string
	 */
	public function get_response_code_by_url( string $url ) {
		return wp_remote_retrieve_response_code( wp_remote_head( esc_url( $url ) ) );
	}

	/**
	 * Get the thumbnail for a YouTube or Vimeo video.
	 */
	public function get_thumbnail( string $source, string $url ): string {
		if ( empty( $source ) || empty( $url ) ) {
			return '';
		}

		$embed_id       = $this->get_embed_id( $source, $url );
		$transient_name = "clarkson_{$source}_thumbnails_{$embed_id}";
		$thumbnail      = get_transient( $transient_name );

		if ( $thumbnail !== false ) {
			return $thumbnail;
		}

		if ( $source === 'youtube' ) {
			$maxres_url = "https://i.ytimg.com/vi/{$embed_id}/maxresdefault.jpg";
			$type       = $this->get_response_code_by_url( $maxres_url ) === 200 ? 'maxresdefault' : 'hqdefault';
			$thumbnail  = esc_url_raw( "https://i.ytimg.com/vi/{$embed_id}/{$type}.jpg" );
		}

		if ( $source === 'vimeo' ) {
			$attributes = wp_remote_retrieve_body( wp_remote_get( esc_url_raw( "https://vimeo.com/api/v2/video/{$embed_id}.json" ) ) );

			if ( empty( $attributes ) ) {
				$thumbnail = '';
			}

			$attributes = json_decode( $attributes );

			if ( ! is_array( $attributes ) || ! isset( $attributes[0]->title ) ) {
				$thumbnail = '';
			}

			$thumbnail = str_replace( '.webp', '.jpg', $attributes[0]->thumbnail_large );
		}

		set_transient( $transient_name, $thumbnail, MONTH_IN_SECONDS );

		return (string) $thumbnail;
	}

	/**
	 * Returns a bool whether source is an video embed
	 */
	public function is_video_embed( string $source ): bool {
		if ( $source === 'youtube' || $source === 'vimeo' ) {
			return true;
		}

		return false;
	}

	/**
	 * Returns a bool whether source is an audio embed
	 */
	public function is_audio_embed( string $source ): bool {
		if ( $source === 'soundcloud' || $source === 'spotify' || $source === 'mixcloud' ) {
			return true;
		}

		return false;
	}

	/**
	 * Disable cookie notice for vimeo video's with dnt=1
	 */
	public function maybe_disable_notice( string $source, string $html ): bool {
		if ( $source === 'vimeo' ) {
			return strpos( $html, 'dnt=1' ) !== false;
		}

		return false;
	}
}
