<?php
namespace FDMGLabels\Logging\ADvance;

use FDMGLabels\Logging\BaseLogger;

class ADvanceLogger extends BaseLogger {

	protected function get_logger_id(): string {
		return 'fdmg_advance';
	}
}
