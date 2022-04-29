<?php
namespace Clarkson\Utilities;

use DateTime;
use DateTimeInterface;

class I18n {
	public function format_date( string $format = '', DateTimeInterface $date = null, int $gmt_offset_hours = null ): string {
		if ( is_null( $date ) ) {
			$date = new DateTime();
		}

		if ( empty( $format ) ) {
			$format = sprintf( '%s %s', get_option( 'date_format' ), get_option( 'time_format' ) );
		}

		if ( is_null( $gmt_offset_hours ) ) {
			$gmt_offset_hours = wp_timezone()->getOffset( $date ) / HOUR_IN_SECONDS;
		}

		$gmt_offset_seconds = (int) ( $gmt_offset_hours * HOUR_IN_SECONDS );
		return date_i18n( $format, $date->getTimestamp() + $gmt_offset_seconds );
	}
}
