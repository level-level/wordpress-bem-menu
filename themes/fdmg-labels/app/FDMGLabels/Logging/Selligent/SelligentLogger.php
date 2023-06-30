<?php
namespace ImpactInvestor\Logging\Selligent;

use ImpactInvestor\Logging\BaseLogger;

class SelligentLogger extends BaseLogger {

	protected function get_logger_id(): string {
		return 'selligent';
	}

}
