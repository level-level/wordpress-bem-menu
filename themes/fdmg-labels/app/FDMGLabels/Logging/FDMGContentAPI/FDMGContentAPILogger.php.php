<?php
namespace FDMGLabels\Logging\FDMGContentAPI;

use FDMGLabels\Logging\BaseLogger;

class FDMGContentAPILogger extends BaseLogger {

	protected function get_logger_id(): string {
		return 'fdmg-content-api';
	}

}
