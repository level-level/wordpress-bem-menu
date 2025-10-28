<?php

namespace FDMGLabels\GravityForms;

use FDMGLabels\GravityForms\ADvance\ADvanceOptinNewsletterOnSubmission;
use FDMGLabels\GravityForms\ConfirmationPageParams;
use FDMGLabels\GravityForms\FormSettings\Init as FormSettingsInit;

class Init {
	public function register_hooks(): void {
		( new ConfirmationPageParams() )->register_hooks();
		( new FormSettingsInit() )->register_hooks();
		( new ADvanceOptinNewsletterOnSubmission() )->register_hooks();
	}
}
