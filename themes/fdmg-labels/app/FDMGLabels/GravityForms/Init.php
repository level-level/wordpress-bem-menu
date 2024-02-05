<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\Selligent\OptinNewsletterOnSubmission;
use FDMGLabels\GravityForms\ConfirmationPageParams;

class Init {

	public function register_hooks(): void {
		( new OptinNewsletterOnSubmission() )->register_hooks();
		( new ConfirmationPageParams() )->register_hooks();
	}
}
