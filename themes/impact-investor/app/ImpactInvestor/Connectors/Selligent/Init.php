<?php

namespace ImpactInvestor\Connectors\Selligent;

use ImpactInvestor\Connectors\Selligent\Admin\NewsLetter;
use ImpactInvestor\Connectors\Selligent\Admin\Settings;
use ImpactInvestor\Connectors\Selligent\Admin\Status;

class Init {
	public function register_hooks(): void {
		( new Settings() )->register_hooks();
		( new Status() )->register_hooks();
		( new NewsLetter() )->register_hooks();
	}
}
