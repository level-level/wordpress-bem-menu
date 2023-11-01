<?php
namespace FDMGLabels\Newsletter;

class Init {

	public function register_hooks(): void {
		$newsletter_service = new NewsletterSenderService();
		( new PreviewNewsletterScheduler( $newsletter_service ) )->register_hooks();
		( new SendNewsletterScheduler( $newsletter_service ) )->register_hooks();

		( new MergeTagsMetabox() )->register_hooks();
	}
}
