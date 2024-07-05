<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\ConfirmationPageParams;
use FDMGLabels\GravityForms\FormSettings\Init as FormSettingsInit;
use FDMGLabels\GravityForms\Selligent\OptinNewsletterOnSubmission;

class Init {
	public function register_hooks(): void {
		( new ConfirmationPageParams() )->register_hooks();
		( new FormSettingsInit() )->register_hooks();
		( new OptinNewsletterOnSubmission() )->register_hooks();
	}
}
