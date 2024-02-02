<?php
namespace FDMGLabels\Logging\Selligent;

use FDMGLabels\Logging\BaseLogger;

class SelligentLogger extends BaseLogger {

	protected function get_logger_id(): string {
		return 'selligent';
	}
}
