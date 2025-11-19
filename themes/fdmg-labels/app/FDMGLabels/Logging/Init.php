<?php
namespace FDMGLabels\Logging;

use FDMGLabels\Logging\ADvance\MetaBoxes as ADvanceMetaBoxes;
use FDMGLabels\Logging\FDMGContentAPI\MetaBoxes as FDMGContentAPIMetaBoxes;
use FDMGLabels\Logging\Selligent\MetaBoxes as SelligentMetaBoxes;

class Init {

	public function register_hooks(): void {
		( new FDMGContentAPIMetaBoxes() )->register_hooks();
		( new ADvanceMetaBoxes() )->register_hooks();
	}
}
