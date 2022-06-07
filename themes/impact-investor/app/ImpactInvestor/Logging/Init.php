<?php
namespace ImpactInvestor\Logging;

use ImpactInvestor\Logging\Selligent\MetaBoxes as SelligentMetaBoxes;

class Init {

	public function register_hooks(): void {
		( new SelligentMetaBoxes() )->register_hooks();
	}
}
