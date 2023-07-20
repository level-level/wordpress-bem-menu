<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\Selligent\OptinNewsletterOnSubmission;

class Init {

	public function register_hooks(): void {
		( new OptinNewsletterOnSubmission() )->register_hooks();
	}
}
