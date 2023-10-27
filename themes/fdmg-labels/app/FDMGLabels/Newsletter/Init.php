<?php
namespace FDMGLabels\Newsletter;

class Init {

	public function register_hooks(): void {
		( new SendNewsletter() )->register_hooks();
		( new MergeTags() )->register_hooks();
	}
}
