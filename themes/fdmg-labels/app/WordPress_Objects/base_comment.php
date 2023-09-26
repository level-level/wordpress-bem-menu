<?php

namespace Clarkson_Core\WordPress_Object;

class base_comment extends Clarkson_Comment {

	/**
	 * Display the publish date of the comment in a human readable format if it's been published for less than 24 hours
	 */
	public function get_human_readable_date(): string {
		$date_str     = $this->get_date();
		$date         = (int) $date_str;
		$current_time = time();
		$time_diff    = $current_time - $date;

		if ( $time_diff >= DAY_IN_SECONDS ) {
			return date_i18n( get_option( 'date_format' ), $date );
		} else {
			return sprintf( __( '%s <span class="sr-only">ago</span>', 'fdmg-labels' ), human_time_diff( $date, $current_time ) );
		}
	}
}
