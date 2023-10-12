<?php

namespace FDMGLabels\Import\Helpers;

class ContentParser {

	public function format_value( string $value, string $default = '' ): string {
		if ( $value === 'NULL' ) {
			return $default;
		}
		return $value;
	}

}
