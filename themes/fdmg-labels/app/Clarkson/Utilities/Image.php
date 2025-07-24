<?php

namespace Clarkson\Utilities;

use Exception;
use Monolog\Registry;
use WP_Error;

class Image {

	public const IMAGE_DOWNLOAD_TIMEOUT = 10;

	/**
	 * Download image from url, and save it in the media library.
	 *
	 * @param string $image_url The url of the image to download
	 * @param array $args (optional) Arguments
	 *
	 * @return int Image ID
	 *
	 * @psalm-suppress UnresolvableInclude
	 */
	public function download_image( string $image_url, array $args = array() ): int {
		$image_url = $this->convert_url( $image_url );

		// Check if is valid url
		if ( ! filter_var( $image_url, FILTER_VALIDATE_URL ) ) {
			return 0;
		}

		// Add query vars to the url
		if ( isset( $args['query_args'] ) && is_array( $args['query_args'] ) ) {
			foreach ( $args['query_args'] as $query_arg_key => $query_arg_value ) {
				$image_url = remove_query_arg( $query_arg_key, $image_url );
				$image_url = add_query_arg( $query_arg_key, rawurlencode( $query_arg_value ), $image_url );
			}
		}

		$attachment_id = $this->get_attachment_id_by_external_id( $image_url );
		if ( $attachment_id ) {
			return $attachment_id;
		}

		// Load required files for downloading with media_sideload_image to work
		// See https://codex.wordpress.org/Function_Reference/media_sideload_image Notes header
		require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/media.php';
		require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/file.php';
		require_once trailingslashit( ABSPATH ) . 'wp-admin/includes/image.php';

		add_filter( 'http_request_timeout', array( $this, 'set_download_timeout' ) ); // phpcs:ignore WordPressVIPMinimum.Hooks.RestrictedHooks.http_request_timeout
		// Download the image
		try {
			$attachment_id = media_sideload_image( $image_url, 0, null, 'id' );
			remove_filter( 'http_request_timeout', array( $this, 'set_download_timeout' ) );
			if ( ! $attachment_id || $attachment_id instanceof WP_Error ) {
				return 0;
			}
			update_post_meta( (int) $attachment_id, 'external_source', $image_url );
		} catch ( Exception $e ) {
			remove_filter( 'http_request_timeout', array( $this, 'set_download_timeout' ) );
			return 0;
		}

		return (int) $attachment_id;
	}

	/**
	 * Get attachment id by external id
	 *
	 * @param string $external_id External ID. Most of the time, this is the image source url
	 *
	 * @return int Attachment ID
	 *
	 */
	protected function get_attachment_id_by_external_id( string $external_id ): int {
		$args = array(
			'post_type'      => 'attachment',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'     => 'external_source',
					'value'   => $external_id,
					'compare' => '=',
				),
			),
		);

		/** @var int[] */
		$results = get_posts( $args );
		return (int) array_shift( $results );
	}

	/**
	 * Set post thumbnail
	 *
	 * @param int $post_id The post id
	 * @param int|string $attachment The attachment id or url
	 * @param array $args Arguments
	 *
	 * @return int Attachment ID
	 */
	public function set_post_thumbnail( int $post_id, $attachment, array $args = array() ): int {
		$attachment_id = $attachment;
		if ( ! is_numeric( $attachment ) ) {
			$attachment_id = $this->download_image( $attachment, $args );
		}
		if ( is_int( $attachment_id ) ) {
			set_post_thumbnail( $post_id, $attachment_id );
		}
		return (int) $attachment_id;
	}

	/**
	 * Convert the url string to a readable format which doesn't break when used in the download_image function.
	 *
	 * @param string $image_url
	 *
	 * @return string New image url
	 */
	public function convert_url( string $image_url ): string {
		// If url starts with //, prepend http://
		if ( strpos( $image_url, '//' ) === 0 ) {
			$image_url = 'http:' . $image_url;
		}
		return $image_url;
	}

	/**
	 * Get image objects from attachment objects/ids
	 *
	 * @param int[]|\WP_Post[] $attachments
	 *
	 * @return array[] Image objects
	 */
	public function get_image_objects( array $attachments ): array {
		$attachments = array();
		foreach ( $attachments as $attachment ) {
			$image_object = $this->get_image_object( $attachment );
			if ( $image_object ) {
				$attachments[] = $image_object;
			}
		}
		return $attachments;
	}

	/**
	 * Build an image object for display
	 *
	 * @param int|\WP_Post $attachment Attachment object or id
	 *
	 * @return array
	 */
	public function get_image_object( $attachment = null ): ?array {
		if ( ! $attachment ) {
			return null;
		}

		$attachment_data = acf_get_attachment( $attachment );
		if ( false === $attachment_data ) {
			return null;
		}

		return $attachment_data;
	}

	/**
	 * Get all available image sizes data in the given aspect ratio.
	 *
	 * @param int $attachment_id
	 * @param int $ratio_width
	 * @param int $ratio_height
	 * @return array<string, array{source: string, width: int, height: int}>
	 */
	protected function get_aspect_ratio_image_sizes( int $attachment_id, int $ratio_width, int $ratio_height ): array {
		if ( ! extension_loaded( 'gmp' ) ) {
			Registry::wordpress()->warning(
				'Could not get available aspect ratio image sizes because the GMP PHP extension is not installed.', array(
					'attachment_id' => $attachment_id,
					'ratio_width'   => $ratio_width,
					'ratio_height'  => $ratio_height,
				)
			);
			return array();
		}

		$common_deviser = gmp_intval( gmp_gcd( $ratio_width, $ratio_height ) );
		$ratio_width    = $ratio_width / $common_deviser;
		$ratio_height   = $ratio_height / $common_deviser;

		return array_filter(
			$this->get_available_image_sizes( $attachment_id ),
			function ( array $image_size_data ) use ( $ratio_width, $ratio_height ): bool {
				$common_deviser    = gmp_intval( gmp_gcd( $image_size_data['width'], $image_size_data['height'] ) );
				$size_ratio_width  = $image_size_data['width'] / $common_deviser;
				$size_ratio_height = $image_size_data['height'] / $common_deviser;
				return $size_ratio_width === $ratio_width && $size_ratio_height === $ratio_height;
			}
		);
	}

	/**
	 * Get all available image sizes.
	 *
	 * @param int $attachment_id
	 * @return array<string, array{source: string, width: int, height: int}>
	 */
	protected function get_available_image_sizes( int $attachment_id ): array {
		$sizes      = array_merge( get_intermediate_image_sizes(), array( 'full' ) );
		$sizes_data = array();
		foreach ( $sizes as $size ) {
			$src = wp_get_attachment_image_src( $attachment_id, $size );
			if ( ! $src ) {
				continue;
			}
			$sizes_data[ $size ] = array(
				'source' => $src[0],
				'width'  => $src[1],
				'height' => $src[2],
			);
		}

		return $sizes_data;
	}

	/**
	 * Modify the download timeout
	 *
	 * @param float $timeout in seconds
	 *
	 * @return float
	 */
	public function set_download_timeout( float $timeout ): float {
		$timeout = (float) self::IMAGE_DOWNLOAD_TIMEOUT;
		return $timeout;
	}
}
