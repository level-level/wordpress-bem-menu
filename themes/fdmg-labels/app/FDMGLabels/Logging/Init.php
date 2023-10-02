<?php
namespace FDMGLabels\Logging;

use FDMGLabels\Logging\FDMGContentAPI\MetaBoxes as FDMGContentAPIMetaBoxes;
use FDMGLabels\Logging\Selligent\MetaBoxes as SelligentMetaBoxes;

class Init {

	public function register_hooks(): void {
		( new SelligentMetaBoxes() )->register_hooks();
		( new FDMGContentAPIMetaBoxes() )->register_hooks();
	}
}
