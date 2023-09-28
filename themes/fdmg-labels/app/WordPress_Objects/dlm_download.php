<?php

namespace Clarkson_Core\WordPress_Object;

use Exception;

class dlm_download extends base_object {
	public static $type = 'dlm_download';

	/**
	 * Gets the DLM_Download object from the repository linked to this post object.
	 */
	public function get_file(): ?\DLM_Download {
		if ( ! class_exists( \DLM_Download::class ) || ! function_exists( 'download_monitor' ) ) {
			return null;
		}

		try {
			return download_monitor()->service( 'download_repository' )->retrieve_single( $this->get_id() );
		} catch ( Exception $e ) {
			return null;
		}
	}
}
