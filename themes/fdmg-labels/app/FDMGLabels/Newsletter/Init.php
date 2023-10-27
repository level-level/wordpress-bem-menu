<?php
namespace FDMGLabels\Newsletter;

class Init {

	public function register_hooks(): void {
		( new PreviewNewsletterScheduler() )->register_hooks();
		// ( new SendNewsletterScheduler() )->register_hooks();
	}
}
