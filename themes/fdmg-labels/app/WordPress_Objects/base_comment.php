<?php

namespace Clarkson_Core\WordPress_Object;

class base_comment extends Clarkson_Comment {

	/**
	 * Display the publish date of the comment in a human readable format if it's been published for less than 24 hours
	 */
	public function get_human_readable_date(): string {
		$date         = $this->get_date();
		$current_time = current_time( 'timestamp' );
		$time_diff    = $current_time - $date; // Calculate the time difference in seconds

		if ( $time_diff >= 86400 ) { // 86400 seconds = 24 hours
			return date_i18n( get_option( 'date_format' ), $date );
		} else {
			return sprintf( _x( '%s <span class="sr-only">ago</span>', 'Date', 'fdmg-labels' ), human_time_diff( $date, $current_time ) );
		}
	}
}
